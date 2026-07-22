// resources/js/pwa.js
//
// Registers the service worker (public/sw.js). Import this once from your
// main entry file (resources/js/app.js) — see SETUP.md step 7.

export function registerServiceWorker() {
  if (!("serviceWorker" in navigator)) {
    return;
  }

  window.addEventListener("load", () => {
    navigator.serviceWorker.register("/sw.js").catch((err) => {
      console.warn("Service worker registration failed:", err);
    });
  });
}
