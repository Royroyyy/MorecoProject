function getCurrentUser() {
  const s = sessionStorage.getItem("moreco_session");
  return s ? JSON.parse(s) : null;
}

function isLoggedIn() {
  return !!sessionStorage.getItem("moreco_session");
}

function cacheUser(user) {
  sessionStorage.setItem("moreco_session", JSON.stringify(user));
}

function clearSession() {
  sessionStorage.removeItem("moreco_session");
}

function getMembershipStatus() {
  const user = getCurrentUser();
  if (!user) return "guest";
  if ([ "admin", "clerk", "loan_officer", "member" ].includes(user.role)) return "approved";
  return sessionStorage.getItem("moreco_mem_status") || "registered";
}

function cacheMembershipStatus(status) {
  sessionStorage.setItem("moreco_mem_status", status);
}

async function apiGet(url) {
  try {
    const res = await fetch(url);
    return res.json();
  } catch (e) {
    return {
      success: false,
      message: "Network error. Check your connection.",
      data: null
    };
  }
}

async function apiPost(url, data) {
  try {
    const res = await fetch(url, {
      method: "POST",
      headers: {
        "Content-Type": "application/json"
      },
      body: JSON.stringify(data)
    });
    return res.json();
  } catch (e) {
    return {
      success: false,
      message: "Network error. Check your connection.",
      data: null
    };
  }
}

async function checkSession() {
  const cached = sessionStorage.getItem("moreco_session");
  if (cached) return JSON.parse(cached);
  try {
    const res = await fetch("api/auth/session.php");
    const data = await res.json();
    if (data.data?.user) {
      cacheUser(data.data.user);
      return data.data.user;
    }
  } catch (e) {}
  return null;
}

function renderNav(activePage) {
  const user = getCurrentUser();
  const status = getMembershipStatus();
  const pages = user ? [ {
    key: "home",
    label: "Home",
    href: "index.html"
  }, {
    key: "announcements",
    label: "Announcements",
    href: "announcements.html"
  }, {
    key: "benefits",
    label: "Benefits",
    href: "benefits.html"
  }, {
    key: "events",
    label: "Events",
    href: "events.html"
  } ] : [ {
    key: "home",
    label: "Home",
    href: "index.html"
  }, {
    key: "events",
    label: "Events",
    href: "events.html"
  }, {
    key: "benefits",
    label: "Benefits & Services",
    href: "benefits.html"
  }, {
    key: "announcements",
    label: "Announcements",
    href: "announcements.html"
  }, {
    key: "about",
    label: "About",
    href: "about.html"
  } ];
  const memberLinks = user ? `\n    <li><a href="dashboard.html"    class="${activePage === "dashboard" ? "active" : ""}">Dashboard</a></li>\n    <li><a href="withdrawals.html"  class="${activePage === "withdrawals" ? "active" : ""}">Withdrawals</a></li>\n    <li><a href="transactions.html" class="${activePage === "transactions" ? "active" : ""}">Transactions</a></li>\n  ` : "";
  const links = pages.map(p => `<li><a href="${p.href}" class="${p.key === activePage ? "active" : ""}">${p.label}</a></li>`).join("");
  const adminLink = user && [ "admin", "clerk", "loan_officer" ].includes(user.role) ? `<li><a href="admin.html" class="nav-admin-link" title="Admin Panel">⚙️ Admin</a></li>` : "";
  const applyNavBtn = status !== "approved" ? `<li>\n         <a href="apply.html" class="nav-apply-btn ${activePage === "apply" ? "active" : ""}" title="Complete your membership">\n           📋 Apply for Membership\n         </a>\n       </li>` : "";
  const notifBell = user ? `<li>\n         <a href="notifications.html" class="nav-bell" id="nav-bell" title="Notifications">\n           🔔 <span class="notif-badge" id="notif-badge" style="display:none;">0</span>\n         </a>\n       </li>` : "";
  const authSection = user ? `<li>\n         <a href="account.html" class="nav-avatar" title="${user.first_name} ${user.last_name}">\n           ${(user.first_name || "?")[0]}${(user.last_name || "?")[0]}\n         </a>\n       </li>` : `<li><a href="login.html" class="btn-login">Login</a></li>`;
  return `\n    <nav class="navbar">\n      <a href="index.html" class="nav-brand">\n        <img src="assets/images/moreco-logo.png" alt="MORECO Logo"\n             onerror="this.style.display='none'">\n        <span>MORECO</span>\n      </a>\n      <ul class="nav-links" id="navLinks">\n        ${links}\n        ${memberLinks}\n        ${adminLink}\n        ${applyNavBtn}\n        ${notifBell}\n        ${authSection}\n      </ul>\n      <div class="hamburger" onclick="toggleNav()" id="hamburger">\n        <span></span><span></span><span></span>\n      </div>\n    </nav>\n  `;
}

function toggleNav() {
  document.getElementById("navLinks").classList.toggle("open");
  document.getElementById("hamburger").classList.toggle("open");
}

document.addEventListener("click", e => {
  const nav = document.getElementById("navLinks");
  const ham = document.getElementById("hamburger");
  if (nav && ham && !nav.contains(e.target) && !ham.contains(e.target)) {
    nav.classList.remove("open");
    ham.classList.remove("open");
  }
});

function renderFooter() {
  return `\n    <footer class="site-footer">\n      <div class="footer-inner">\n        <div class="footer-brand">\n          <strong>MORECO</strong>\n          <span>Morong Retailers and Community Multipurpose Cooperative</span>\n        </div>\n        <div class="footer-links">\n          <a href="about.html">About</a>\n          <a href="announcements.html">Announcements</a>\n          <a href="events.html">Events</a>\n          <a href="benefits.html">Benefits & Services</a>\n          <a href="apply.html">Apply for Membership</a>\n        </div>\n        <div class="footer-copy">\n          &copy; ${(new Date).getFullYear()} MORECO — Morong, Rizal, Philippines\n        </div>\n      </div>\n    </footer>\n  `;
}

function showMembershipPopup(triggerSource) {
  const user = getCurrentUser();
  const status = getMembershipStatus();
  if (!user || status === "approved") return;
  const key = `moreco_popup_shown_${triggerSource}`;
  if (sessionStorage.getItem(key)) return;
  sessionStorage.setItem(key, "1");
  const old = document.getElementById("membership-popup-overlay");
  if (old) old.remove();
  const overlay = document.createElement("div");
  overlay.id = "membership-popup-overlay";
  overlay.className = "membership-popup-overlay";
  overlay.innerHTML = `\n    <div class="membership-popup" role="dialog" aria-modal="true" aria-labelledby="mp-title">\n      <div class="mp-header">\n        <div class="mp-icon">📋</div>\n        <h2 id="mp-title">Complete Your Membership Application</h2>\n      </div>\n      <div class="mp-body">\n        <p>\n          Welcome to <strong>MORECO</strong>! Your website account has been created successfully.\n        </p>\n        <p>\n          However, <strong>website registration alone does not grant full MORECO membership.</strong>\n          To unlock all member-exclusive benefits and services — including loans, savings programs,\n          insurance coverage, and more — you need to complete the official membership application process.\n        </p>\n        <div class="mp-status-row">\n          <div class="mp-status-step mp-step-done">\n            <span class="mp-step-icon">✅</span>\n            <span>Account Created</span>\n          </div>\n          <div class="mp-step-arrow">→</div>\n          <div class="mp-status-step mp-step-current">\n            <span class="mp-step-icon">📋</span>\n            <span>Apply for Membership</span>\n          </div>\n          <div class="mp-step-arrow">→</div>\n          <div class="mp-status-step mp-step-pending">\n            <span class="mp-step-icon">✅</span>\n            <span>Full Member</span>\n          </div>\n        </div>\n      </div>\n      <div class="mp-footer">\n        <a href="apply.html" class="btn btn-primary mp-apply-btn">\n          📋 Apply for Membership\n        </a>\n        <button class="btn btn-secondary" onclick="closeMembershipPopup()">\n          Maybe Later\n        </button>\n      </div>\n    </div>\n  `;
  overlay.addEventListener("click", e => {
    if (e.target === overlay) closeMembershipPopup();
  });
  document.body.appendChild(overlay);
  requestAnimationFrame(() => overlay.classList.add("active"));
  document.body.style.overflow = "hidden";
}

function closeMembershipPopup() {
  const overlay = document.getElementById("membership-popup-overlay");
  if (!overlay) return;
  overlay.classList.remove("active");
  setTimeout(() => {
    overlay.remove();
    document.body.style.overflow = "";
  }, 280);
}

function showToast(message, type = "info", duration = 3500) {
  let container = document.getElementById("toast-container");
  if (!container) {
    container = document.createElement("div");
    container.id = "toast-container";
    container.className = "toast-container";
    document.body.appendChild(container);
  }
  const icons = {
    success: "✅",
    error: "❌",
    info: "ℹ️",
    warning: "⚠️"
  };
  const toast = document.createElement("div");
  toast.className = `toast toast-${type}`;
  toast.innerHTML = `<span class="toast-icon">${icons[type] || "ℹ️"}</span><span>${message}</span>`;
  container.appendChild(toast);
  requestAnimationFrame(() => toast.classList.add("show"));
  setTimeout(() => {
    toast.classList.remove("show");
    setTimeout(() => toast.remove(), 300);
  }, duration);
}

function roleBadge(role) {
  const map = {
    admin: {
      label: "⚙️ Admin",
      cls: "badge-blue"
    },
    clerk: {
      label: "🖥️ Clerk",
      cls: "badge-purple"
    },
    loan_officer: {
      label: "💼 Loan Officer",
      cls: "badge-gold"
    },
    member: {
      label: "✅ Member",
      cls: "badge-green"
    },
    applicant: {
      label: "📋 Applicant",
      cls: "badge-gray"
    }
  };
  const r = map[role] || {
    label: role,
    cls: "badge-gray"
  };
  return `<span class="badge ${r.cls}">${r.label}</span>`;
}

function statusBadge(status) {
  const map = {
    pending: {
      label: "⏳ Pending",
      cls: "badge-gray"
    },
    under_review: {
      label: "🔍 Under Review",
      cls: "badge-gold"
    },
    approved: {
      label: "✅ Approved",
      cls: "badge-green"
    },
    rejected: {
      label: "❌ Rejected",
      cls: "badge-red"
    },
    released: {
      label: "💸 Released",
      cls: "badge-blue"
    },
    upcoming: {
      label: "🟢 Upcoming",
      cls: "badge-green"
    },
    completed: {
      label: "⚫ Completed",
      cls: "badge-gray"
    },
    scheduled: {
      label: "📅 Scheduled",
      cls: "badge-gold"
    },
    active: {
      label: "✅ Active",
      cls: "badge-green"
    },
    scanned: {
      label: "📱 Scanned",
      cls: "badge-blue"
    },
    expired: {
      label: "⏰ Expired",
      cls: "badge-red"
    }
  };
  const s = map[status] || {
    label: status,
    cls: "badge-gray"
  };
  return `<span class="badge ${s.cls}">${s.label}</span>`;
}

function formatDate(dateStr) {
  if (!dateStr) return "—";
  const d = new Date(dateStr + "T00:00");
  return d.toLocaleDateString("en-PH", {
    year: "numeric",
    month: "long",
    day: "numeric"
  });
}

function formatMoney(amount) {
  return "₱" + parseFloat(amount || 0).toLocaleString("en-PH", {
    minimumFractionDigits: 2
  });
}

function formatDateTime(dtStr) {
  if (!dtStr) return "—";
  const d = new Date(dtStr);
  return d.toLocaleString("en-PH", {
    year: "numeric",
    month: "short",
    day: "numeric",
    hour: "2-digit",
    minute: "2-digit"
  });
}

async function loadNotifBadge() {
  if (!isLoggedIn()) return;
  try {
    const res = await fetch("api/notifications/get_notifications.php?unread_only=1");
    const data = await res.json();
    if (data.success) {
      const count = data.data?.length || 0;
      const badge = document.getElementById("notif-badge");
      if (badge) {
        badge.textContent = count > 9 ? "9+" : count;
        badge.style.display = count > 0 ? "inline-block" : "none";
      }
    }
  } catch (e) {}
}

function emptyState(icon, message) {
  return `\n    <div class="empty-state">\n      <div class="empty-icon">${icon}</div>\n      <p>${message}</p>\n    </div>\n  `;
}

function loadingSpinner() {
  return `<div class="loading-spinner"><div class="spinner"></div><p>Loading...</p></div>`;
}