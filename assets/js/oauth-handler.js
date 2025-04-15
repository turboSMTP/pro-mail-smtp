/**
 * Free Mail SMTP OAuth Handler
 * This script runs on all WordPress admin pages to catch OAuth redirects
 */
(function () {
  console.log("Free Mail SMTP OAuth Handler loaded");
  // Execute immediately to capture parameters as early as possible
  function handleOAuthCallback() {
    try {
      // Get parameters from both the URL query string and hash fragment
      var queryParams = new URLSearchParams(window.location.search);
      var hashParams = new URLSearchParams(window.location.hash.substring(1));
      var code = queryParams.get("code") || hashParams.get("code");
      var state = queryParams.get("state") || hashParams.get("state");
      if (code && state && isOAuthProvider(state)) {
        // Show processing notification
        var notificationDiv = document.createElement("div");
        notificationDiv.style.cssText =
          "position:fixed;top:32px;right:20px;background:#fff;padding:10px 20px;border-left:4px solid #46b450;box-shadow:0 1px 1px rgba(0,0,0,.04);z-index:999999";
        notificationDiv.innerHTML =
          "Processing " + state + " authentication...";
        document.body.appendChild(notificationDiv);

        // Process the OAuth callback
        processOAuthCallback(code, state, notificationDiv);
      }
    } catch (error) {
      console.error("Error in Free Mail SMTP OAuth handler:", error);
    }
  }

  function isOAuthProvider(state) {
    var supportedProviders = ["gmail", "outlook"];
    return supportedProviders.includes(state.toLowerCase());
  }

  function processOAuthCallback(code, state, notificationDiv) {
    if (
      typeof FreeMailSMTPOAuth === "undefined" ||
      typeof FreeMailSMTPOAuth.ajaxUrl === "undefined" ||
      typeof FreeMailSMTPOAuth.nonce === "undefined"
    ) {
      console.error(
        "FreeMailSMTP Error: Localization data (FreeMailSMTPOAuth) not available."
      );
      return;
    }

    var ajaxUrl = FreeMailSMTPOAuth.ajaxUrl; 
    var nonce = FreeMailSMTPOAuth.nonce; 

    if (!ajaxUrl) {
      console.error(
        "FreeMailSMTP Error: ajaxUrl is missing from localization data."
      );
      return;
    }

    jQuery.ajax({
      url: ajaxUrl,
      type: "POST",
      data: {
        action: "free_mail_smtp_set_oauth_token",
        code: code,
        nonce: nonce,
        provider_type: state,
      },
      success: function (response) {
        if (response.success) {
          notificationDiv.innerHTML =
            state + " connected successfully! Redirecting...";
          notificationDiv.style.borderLeftColor = "#46b450";

          if (
            typeof FreeMailSMTPOAuth !== "undefined" &&
            FreeMailSMTPOAuth.redirectUrl
          ) {
            window.location.href = FreeMailSMTPOAuth.redirectUrl;
          } else {
            var newUrl =
              window.location.protocol +
              "//" +
              window.location.host +
              window.location.pathname;
            window.history.replaceState({}, document.title, newUrl);
            setTimeout(function () {
              window.location.reload();
            }, 1000);
          }
        } else {
          console.error("Failed to connect " + state + ":", response.data);
          notificationDiv.innerHTML =
            "Failed to connect " +
            state +
            ": " +
            (response.data || "Unknown error");
          notificationDiv.style.borderLeftColor = "#dc3232";
        }
      },
      error: function (jqXHR, textStatus, errorThrown) {
        console.error("AJAX request failed:", textStatus, errorThrown);
        notificationDiv.innerHTML = "Connection error: " + textStatus;
        notificationDiv.style.borderLeftColor = "#dc3232";
      },
    });
  }

  handleOAuthCallback();

  document.addEventListener("DOMContentLoaded", handleOAuthCallback);
})();
