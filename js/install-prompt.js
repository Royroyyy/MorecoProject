let deferredInstallPrompt = null;

window.addEventListener("beforeinstallprompt", e => {
  e.preventDefault();
  deferredInstallPrompt = e;
});

function isRunningStandalone() {
  return window.matchMedia("(display-mode: standalone)").matches || window.navigator.standalone === true;
}

function detectPlatform() {
  const ua = navigator.userAgent || "";
  if (/iPhone|iPad|iPod/.test(ua)) return "ios";
  if (/Android/.test(ua)) return "android";
  return "windows";
}

function renderInstallButton() {
  if (isRunningStandalone()) return "";
  return `\n    <button id="moreco-install-btn" onclick="handleInstallClick()" class="moreco-install-btn">\n      📲 Install App\n    </button>\n  `;
}

function mountInstallButton() {
  if (isRunningStandalone()) return;
  const existing = document.getElementById("moreco-install-btn-wrap");
  if (existing) return;
  const wrap = document.createElement("div");
  wrap.id = "moreco-install-btn-wrap";
  wrap.innerHTML = renderInstallButton();
  document.body.appendChild(wrap);
}

async function handleInstallClick() {
  if (deferredInstallPrompt) {
    deferredInstallPrompt.prompt();
    const choice = await deferredInstallPrompt.userChoice;
    deferredInstallPrompt = null;
    if (choice.outcome === "accepted") {
      const btn = document.getElementById("moreco-install-btn-wrap");
      if (btn) btn.remove();
    }
    return;
  }
  openInstallModal();
}

function openInstallModal() {
  const platform = detectPlatform();
  const existing = document.getElementById("install-modal-overlay");
  if (existing) existing.remove();
  const content = {
    windows: {
      icon: "🖥️",
      title: "Install on Windows",
      steps: [ 'Click the <strong>install icon</strong> (⊕) in your browser\'s address bar — or open the menu (⋮) and select <strong>"Install MORECO"</strong>.', "Click <strong>Install</strong> in the popup that appears.", "MORECO will open in its own window and appear in your Start Menu and taskbar, just like a regular app." ],
      note: "Works in Chrome, Edge, and other Chromium-based browsers."
    },
    android: {
      icon: "📱",
      title: "Install on Android",
      steps: [ "Tap the <strong>3-dot menu</strong> (⋮) in the top-right corner of Chrome.", 'Select <strong>"Install app"</strong> or <strong>"Add to Home screen"</strong>.', "Tap <strong>Install</strong> to confirm. The MORECO icon will appear on your home screen and app drawer." ],
      note: "A banner may also appear automatically at the bottom of your screen — just tap it."
    },
    ios: {
      icon: "📱",
      title: "Install on iPhone/iPad",
      steps: [ "Open this website in <strong>Safari</strong> (this must be done in Safari, not Chrome).", 'Tap the <strong>Share button</strong> <span style="display:inline-block;">⬆️</span> at the bottom of the screen.', 'Scroll down and tap <strong>"Add to Home Screen"</strong>.', "Tap <strong>Add</strong> in the top-right corner. The MORECO icon will appear on your home screen." ],
      note: "iOS requires Safari specifically — the install option is not available in Chrome or other browsers on iPhone."
    }
  };
  const tabs = [ "windows", "android", "ios" ];
  const tabLabels = {
    windows: "🖥️ Windows",
    android: "🤖 Android",
    ios: "📱 iOS"
  };
  const overlay = document.createElement("div");
  overlay.id = "install-modal-overlay";
  overlay.className = "install-modal-overlay active";
  overlay.innerHTML = `\n    <div class="install-modal">\n      <div class="install-modal-header">\n        <div class="install-modal-icon">📲</div>\n        <h2>Install MORECO</h2>\n        <p>Get the MORECO app on your device — works offline and opens like a native app.</p>\n      </div>\n      <div class="install-tabs">\n        ${tabs.map(t => `\n          <button class="install-tab ${t === platform ? "active" : ""}" data-tab="${t}" onclick="switchInstallTab('${t}')">\n            ${tabLabels[t]}\n          </button>\n        `).join("")}\n      </div>\n      <div class="install-modal-body" id="install-modal-body">\n        ${renderInstallTabContent(content[platform])}\n      </div>\n      <div class="install-modal-footer">\n        <button class="btn btn-secondary" onclick="closeInstallModal()">Close</button>\n      </div>\n    </div>\n  `;
  overlay.addEventListener("click", e => {
    if (e.target === overlay) closeInstallModal();
  });
  document.body.appendChild(overlay);
  document.body.style.overflow = "hidden";
  window._morecoInstallContent = content;
}

function renderInstallTabContent(data) {
  return `\n    <div class="install-tab-content">\n      <div class="install-tab-icon">${data.icon}</div>\n      <h3>${data.title}</h3>\n      <ol class="install-steps">\n        ${data.steps.map(s => `<li>${s}</li>`).join("")}\n      </ol>\n      <div class="install-note">💡 ${data.note}</div>\n    </div>\n  `;
}

function switchInstallTab(tab) {
  document.querySelectorAll(".install-tab").forEach(btn => {
    btn.classList.toggle("active", btn.dataset.tab === tab);
  });
  const body = document.getElementById("install-modal-body");
  if (body && window._morecoInstallContent) {
    body.innerHTML = renderInstallTabContent(window._morecoInstallContent[tab]);
  }
}

function closeInstallModal() {
  const overlay = document.getElementById("install-modal-overlay");
  if (overlay) overlay.remove();
  document.body.style.overflow = "";
}

document.addEventListener("DOMContentLoaded", mountInstallButton);