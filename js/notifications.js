async function getNotifications(params = {}) {
  const qs = new URLSearchParams(params).toString();
  return apiGet("api/notifications/get_notifications.php" + (qs ? "?" + qs : ""));
}

async function markRead(id) {
  return apiPost("api/notifications/mark_read.php", {
    id: id
  });
}

async function markAllRead() {
  return apiPost("api/notifications/mark_all_read.php", {});
}

async function deleteNotification(id) {
  return apiPost("api/notifications/delete_notification.php", {
    id: id
  });
}

const notifTypes = {
  membership: {
    icon: "📋",
    label: "Membership",
    color: "#60a5fa"
  },
  orientation: {
    icon: "🎓",
    label: "Orientation",
    color: "#c084fc"
  },
  loan: {
    icon: "💰",
    label: "Loan",
    color: "#f0b429"
  },
  withdrawal: {
    icon: "💸",
    label: "Withdrawal",
    color: "#22c55e"
  },
  event: {
    icon: "📅",
    label: "Event",
    color: "#f97316"
  },
  general: {
    icon: "🔔",
    label: "General",
    color: "#94a3b8"
  }
};

function getNotifConfig(type) {
  return notifTypes[type] || notifTypes.general;
}

function renderNotificationCard(n, onMarkRead, onDelete) {
  const cfg = getNotifConfig(n.type);
  const isUnread = !parseInt(n.is_read);
  const isUrgent = (n.type === "orientation" || n.type === "membership") && isUnread;
  const urgentStyle = isUrgent ? `\n        border: 2px solid #FFD700 !important;\n        background: linear-gradient(135deg, rgba(15,30,64,0.95), rgba(26,74,122,0.6)) !important;\n    ` : "";
  const actionBtn = n.link ? (() => {
    const labels = {
      "orientation.html": "📅 Choose Orientation Schedule →",
      "apply.html": "📋 Apply for Membership →",
      "dashboard.html": "🏠 Go to Dashboard →",
      "loans.html": "💰 View Loan Status →"
    };
    const btnLabel = labels[n.link] || "View details →";
    const btnStyle = isUrgent ? "background:#FFD700;color:#0f1e3d;font-weight:800;padding:8px 18px;border-radius:7px;font-size:0.83rem;text-decoration:none;display:inline-block;margin-top:0.65rem;" : "color:var(--gold-dark);font-weight:600;font-size:0.83rem;text-decoration:none;display:inline-block;margin-top:0.5rem;";
    return `<a href="${n.link}" style="${btnStyle}"\n                   onclick="event.stopPropagation(); markRead(${n.id});">${btnLabel}</a>`;
  })() : "";
  return `\n        <div class="notif-card ${isUnread ? "unread" : ""} ${isUrgent ? "notif-urgent" : ""}"\n             id="notif-${n.id}"\n             style="${urgentStyle}"\n             onclick="${onMarkRead}(${n.id})">\n            <div class="notif-icon" style="background:${cfg.color}22; border:1px solid ${cfg.color}44;\n                 ${isUrgent ? "font-size:1.8rem;" : ""}">\n                ${isUrgent ? "🔔" : cfg.icon}\n            </div>\n            <div class="notif-body">\n                <div class="notif-header-row">\n                    <div class="notif-title" style="${isUrgent ? "color:#FFD700;font-size:1rem;" : ""}">\n                        ${n.title}\n                    </div>\n                    <div class="notif-actions">\n                        ${isUnread ? '<span class="unread-dot"></span>' : ""}\n                        <button class="notif-delete-btn"\n                                onclick="event.stopPropagation(); ${onDelete}(${n.id})"\n                                title="Delete">✕</button>\n                    </div>\n                </div>\n                <div class="notif-message">${n.message}</div>\n                <div class="notif-footer">\n                    <span class="notif-type-badge" style="color:${cfg.color};">\n                        ${cfg.icon} ${cfg.label}\n                    </span>\n                    <span class="notif-time">${formatDateTime(n.created_at)}</span>\n                </div>\n                ${actionBtn}\n            </div>\n        </div>\n    `;
}

function renderNotifDropdown(notifications) {
  if (!notifications.length) {
    return `<div style="padding:1.5rem;text-align:center;color:var(--text-muted);font-size:0.85rem;">\n                    No notifications yet.\n                </div>`;
  }
  return notifications.slice(0, 6).map(n => {
    const cfg = getNotifConfig(n.type);
    const isUnread = !parseInt(n.is_read);
    return `\n            <div class="dropdown-notif-item ${isUnread ? "unread" : ""}"\n                 onclick="markRead(${n.id}); ${n.link ? `window.location.href='${n.link}'` : ""}">\n                <div class="dropdown-notif-icon">${cfg.icon}</div>\n                <div class="dropdown-notif-body">\n                    <div class="dropdown-notif-title">${n.title}</div>\n                    <div class="dropdown-notif-time">${formatDateTime(n.created_at)}</div>\n                </div>\n                ${isUnread ? '<div class="dropdown-unread-dot"></div>' : ""}\n            </div>\n        `;
  }).join("");
}

let _notifPollInterval = null;

function startNotifPolling(intervalMs = 2e4) {
  if (_notifPollInterval) return;
  _notifPollInterval = setInterval(loadNotifBadge, intervalMs);
}

function stopNotifPolling() {
  if (_notifPollInterval) {
    clearInterval(_notifPollInterval);
    _notifPollInterval = null;
  }
}