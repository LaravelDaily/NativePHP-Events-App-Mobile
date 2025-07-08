package com.example.androidphp.ui

import android.content.Intent
import android.content.pm.PackageManager
import android.content.res.Configuration
import android.os.Build
import android.os.Bundle
import android.os.Looper
import android.os.Handler
import android.util.Log
import android.view.View
import android.webkit.CookieManager
import androidx.annotation.RequiresApi
import androidx.appcompat.app.AppCompatActivity
import com.example.androidphp.bridge.PHPBridge
import com.example.androidphp.bridge.LaravelEnvironment
import com.example.androidphp.databinding.ActivityMainBinding
import com.example.androidphp.network.WebViewManager
import android.webkit.WebView
import androidx.activity.addCallback
import androidx.core.content.ContextCompat
// Android 15 edge-to-edge support imports
import androidx.activity.enableEdgeToEdge
import androidx.core.view.ViewCompat
import androidx.core.view.WindowInsetsCompat
import com.example.androidphp.R
import com.example.androidphp.utils.NativeActionCoordinator
import com.example.androidphp.utils.WebViewProvider
import com.example.androidphp.security.LaravelCookieStore
import java.io.File
import android.widget.Toast



class MainActivity : AppCompatActivity(), WebViewProvider {
    private lateinit var binding: ActivityMainBinding
    private val phpBridge = PHPBridge(this)
    private lateinit var laravelEnv: LaravelEnvironment<Any?>
    private lateinit var webViewManager: WebViewManager
    private lateinit var coord: NativeActionCoordinator
    private var pendingDeepLink: String? = null
    private var hotReloadWatcherThread: Thread? = null
    private var shouldStopWatcher = false


    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityMainBinding.inflate(layoutInflater)
        setContentView(binding.root)
        supportActionBar?.hide()

        // Android 15 edge-to-edge compatibility fix
        // This ensures the status bar is visible and content is properly positioned
        enableEdgeToEdge()

        // Apply window insets to prevent content from overlapping with system bars
        ViewCompat.setOnApplyWindowInsetsListener(binding.root) { view, insets ->
            val systemBars = insets.getInsets(WindowInsetsCompat.Type.systemBars())
            view.setPadding(systemBars.left, systemBars.top, systemBars.right, systemBars.bottom)
            insets
        }

        binding.splashOverlay.visibility = View.VISIBLE
        setupSplashScreen()
        LaravelCookieStore.init(applicationContext)
        binding.webView.settings.mediaPlaybackRequiresUserGesture = false

        handleDeepLinkIntent(intent)
        initializeEnvironmentAsync {
            binding.splashOverlay.animate()
                .alpha(0f)
                .setDuration(300)
                .withEndAction {
                    binding.splashOverlay.visibility = View.GONE
                }
                .start()

            webViewManager = WebViewManager(this, binding.webView, phpBridge)
            webViewManager.setup()
            coord = NativeActionCoordinator.install(this)

            val target = pendingDeepLink ?: "/"
            val fullUrl = "http://127.0.0.1$target"
            Log.d("DeepLink", "🚀 Loading final URL: $fullUrl")
            binding.webView.loadUrl(fullUrl)

            pendingDeepLink = null
            
            // Start hot reload watcher AFTER Laravel environment is initialized
            startHotReloadWatcher()
        }

        onBackPressedDispatcher.addCallback(this) {
            val webView = binding.webView

            if (webView.canGoBack()) {
                webView.goBack()
            } else {
                finish()
            }
        }
    }

     override fun onConfigurationChanged(newConfig: Configuration) {
        super.onConfigurationChanged(newConfig)
        Log.d("MainActivity", "🌀 Config changed: orientation = ${newConfig.orientation}")
    }

    private fun initializeEnvironmentAsync(onReady: () -> Unit) {
        Thread {
            Log.d("LaravelInit", "📦 Starting async Laravel extraction...")
            laravelEnv = LaravelEnvironment(this)
            laravelEnv.initialize()

            Log.d("LaravelInit", "✅ Laravel environment ready — continuing")

            Handler(Looper.getMainLooper()).post {
                onReady()
            }
        }.start()
    }

    override fun onNewIntent(intent: Intent?) {
        super.onNewIntent(intent)
        handleDeepLinkIntent(intent)
    }

    private fun handleDeepLinkIntent(intent: Intent?) {
        val uri = intent?.data ?: return
        Log.d("DeepLink", "🌐 Received deep link: $uri")

        val path = uri.path ?: "/"
        val query = uri.query
        val laravelUrl = buildString {
            append(path)
            if (!query.isNullOrBlank()) {
                append("?")
                append(query)
            }
        }

        Log.d("DeepLink", "📦 Saving deep link for later: $laravelUrl")
        pendingDeepLink = laravelUrl
    }


    private fun initializeEnvironment() {
        clearAllCookies()
        laravelEnv = LaravelEnvironment(this)
        laravelEnv.initialize()

    }

    fun clearAllCookies() {
        val cookieManager = CookieManager.getInstance()
        cookieManager.removeAllCookies(null)
        cookieManager.flush()
        Log.d("CookieInfo", "All cookies cleared")
    }

    override fun onDestroy() {
        super.onDestroy()
        
        // Stop hot reload watcher thread
        shouldStopWatcher = true
        hotReloadWatcherThread?.interrupt()
        
        laravelEnv.cleanup()
        phpBridge.shutdown()
    }

    override fun getWebView(): WebView {
        return binding.webView
    }

    override fun onRequestPermissionsResult(
        requestCode: Int,
        permissions: Array<out String>,
        grantResults: IntArray
    ) {
        super.onRequestPermissionsResult(requestCode, permissions, grantResults)

        if (requestCode == 1001) {
            if ((grantResults.isNotEmpty() && grantResults[0] == PackageManager.PERMISSION_GRANTED)) {
                Log.d("Permission", "✅ Location permission granted")
                // Optionally re-trigger the location fetch
            } else {
                Log.e("Permission", "❌ Location permission denied")
            }
        }
    }

    private fun startHotReloadWatcher() {
        Log.d("HotReload", "🚀 startHotReloadWatcher() called")
        val debugVersion = isDebugVersion()
        Log.d("HotReload", "🔍 isDebugVersion() returned: $debugVersion")
        
        if (debugVersion) {
            // Configure WebView for development - only disable caching for hot reload
            with(binding.webView.settings) {
                cacheMode = android.webkit.WebSettings.LOAD_NO_CACHE
            }

            Log.d("HotReload", "✅ Hot reload enabled - starting watcher thread")
            
            hotReloadWatcherThread = Thread {
                val appStorageDir = File(filesDir.parent, "app_storage")
                val reloadSignalPath = "${appStorageDir.absolutePath}/laravel/storage/framework/reload_signal.json"
                val reloadFile = File(reloadSignalPath)
                var lastModified: Long = 0

                Log.d("HotReload", "🧵 Hot reload watcher thread started")
                Log.d("HotReload", "🔍 Watching for reload signal at: $reloadSignalPath")

                var loopCount = 0
                while (!shouldStopWatcher && !Thread.currentThread().isInterrupted) {
                    try {
                        // Log every 60 seconds to confirm the watcher is alive
                        if (loopCount % 120 == 0) { // Every 60 seconds (500ms * 120)
                            Log.d("HotReload", "⏰ Hot reload watcher alive - checking for signals...")
                            Log.d("HotReload", "📄 Reload file exists: ${reloadFile.exists()}")
                            if (reloadFile.exists()) {
                                Log.d("HotReload", "📄 Reload file lastModified: ${reloadFile.lastModified()}, tracking: $lastModified")
                            }
                        }
                        
                        if (reloadFile.exists() && reloadFile.lastModified() > lastModified) {
                            lastModified = reloadFile.lastModified()

                            Log.d("HotReload", "🔥 Reload signal detected!")

                            runOnUiThread {
                                // More aggressive cache clearing
                                binding.webView.stopLoading()
                                binding.webView.clearCache(true)
                                binding.webView.clearHistory()
                                binding.webView.clearFormData()

                                // Get current URL and add cache busting
                                val currentUrl = binding.webView.url ?: "http://127.0.0.1/"
                                val separator = if (currentUrl.contains("?")) "&" else "?"
                                val cacheBustUrl = "${currentUrl}${separator}_cb=${System.currentTimeMillis()}"

                                Log.d("HotReload", "🔄 Loading URL with cache bust: $cacheBustUrl")

                                // Small delay then reload with cache busting
                                Handler(Looper.getMainLooper()).postDelayed({
                                    binding.webView.loadUrl(cacheBustUrl)
                                }, 100)

                                Toast.makeText(this@MainActivity, "🔥 Hot reloaded", Toast.LENGTH_SHORT).show()
                            }
                        }

                        Thread.sleep(500)
                        loopCount++
                    } catch (e: InterruptedException) {
                        Log.d("HotReload", "🛑 Hot reload watcher interrupted")
                        break
                    } catch (e: Exception) {
                        Log.e("HotReload", "❌ Hot reload watcher error: ${e.message}", e)
                        Thread.sleep(1000) // Wait longer on error
                        loopCount++
                    }
                }
            }
            hotReloadWatcherThread?.start()
        } else {
            Log.d("HotReload", "❌ Hot reload disabled - not in DEBUG mode")
        }
    }

    private fun isDebugVersion(): Boolean {
        return try {
            val appStorageDir = File(filesDir.parent, "app_storage")
            val versionFile = File(appStorageDir, "laravel/.version")
            
            Log.d("HotReload", "🔍 DEBUG: Checking for version file at: ${versionFile.absolutePath}")
            Log.d("HotReload", "🔍 DEBUG: appStorageDir exists: ${appStorageDir.exists()}")
            Log.d("HotReload", "🔍 DEBUG: laravel dir exists: ${File(appStorageDir, "laravel").exists()}")

            if (versionFile.exists()) {
                val version = versionFile.readText().trim()
                Log.d("HotReload", "🔍 DEBUG: Version file contents: '$version'")
                
                // Robust DEBUG detection - handles DEBUG, "DEBUG", 'DEBUG', debug, etc.
                val cleaned = version.trim().trim('"').trim('\'')
                val isDebug = cleaned.equals("DEBUG", ignoreCase = true)
                
                Log.d("HotReload", "🔍 DEBUG: Cleaned version: '$cleaned'")
                Log.d("HotReload", "🔍 DEBUG: isDebugVersion result: $isDebug")
                isDebug
            } else {
                Log.d("HotReload", "🔍 DEBUG: Version file not found at: ${versionFile.absolutePath}")
                
                // List contents of laravel directory for debugging
                val laravelDir = File(appStorageDir, "laravel")
                if (laravelDir.exists()) {
                    val files = laravelDir.listFiles()
                    if (files != null) {
                        Log.d("HotReload", "🔍 DEBUG: Laravel directory contains: ${files.map { it.name }}")
                    } else {
                        Log.d("HotReload", "🔍 DEBUG: Laravel directory exists but listFiles() returned null")
                    }
                } else {
                    Log.d("HotReload", "🔍 DEBUG: Laravel directory does not exist")
                }
                
                false
            }
        } catch (e: Exception) {
            Log.e("HotReload", "🔍 DEBUG: Error reading version file: ${e.message}")
            false
        }
    }

    private fun setupSplashScreen() {
        try {
            // Check if splash views exist (backward compatibility)
            val splashImageView = binding.root.findViewById<android.widget.ImageView>(
                resources.getIdentifier("splashImage", "id", packageName)
            )
            val splashTextView = binding.root.findViewById<android.widget.TextView>(
                resources.getIdentifier("splashText", "id", packageName)
            )
            
            if (splashImageView == null || splashTextView == null) {
                Log.d("SplashScreen", "📝 Splash views not found - using default behavior (older template)")
                return
            }
            
            // Use dynamic resource resolution to avoid compile-time dependency on splash resource
            val splashResourceId = resources.getIdentifier("splash", "drawable", packageName)
            
            if (splashResourceId != 0) {
                // Splash resource exists - load and display it
                val splashDrawable = try {
                    ContextCompat.getDrawable(this, splashResourceId)
                } catch (e: Exception) {
                    Log.w("SplashScreen", "Failed to load splash drawable: ${e.message}")
                    null
                }
                
                if (splashDrawable != null) {
                    // Show custom splash image
                    splashImageView.setImageDrawable(splashDrawable)
                    splashImageView.visibility = View.VISIBLE
                    splashTextView.visibility = View.GONE
                    Log.d("SplashScreen", "🌅 Using custom splash image")
                } else {
                    // Failed to load drawable - show fallback
                    splashImageView.visibility = View.GONE  
                    splashTextView.visibility = View.VISIBLE
                    Log.d("SplashScreen", "📝 Using default splash text (failed to load drawable)")
                }
            } else {
                // No splash resource exists - show fallback text
                splashImageView.visibility = View.GONE  
                splashTextView.visibility = View.VISIBLE
                Log.d("SplashScreen", "📝 Using default splash text (no splash resource)")
            }
        } catch (e: Exception) {
            // Critical: Don't let splash screen setup crash the app
            Log.w("SplashScreen", "Splash screen setup failed: ${e.message}")
            Log.d("SplashScreen", "📝 Continuing with default splash behavior")
        }
    }
}