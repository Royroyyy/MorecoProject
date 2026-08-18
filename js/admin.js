async function adminGetUsers(params = {}) {
  const qs = new URLSearchParams(params).toString();
  return apiGet("api/users/get_users.php" + (qs ? "?" + qs : ""));
}

async function adminDeleteUser(id) {
  return apiPost("api/users/delete_user.php", {
    id: id
  });
}

async function adminToggleRole(id, role) {
  return apiPost("api/users/toggle_role.php", {
    id: id,
    role: role
  });
}

async function adminToggleActive(id) {
  return apiPost("api/users/toggle_active.php", {
    id: id
  });
}

async function adminGetEvents(params = {}) {
  const qs = new URLSearchParams(params).toString();
  return apiGet("api/events/get_events.php" + (qs ? "?" + qs : ""));
}

async function adminCreateEvent(data) {
  return apiPost("api/events/create_event.php", data);
}

async function adminUpdateEvent(data) {
  return apiPost("api/events/update_event.php", data);
}

async function adminDeleteEvent(id) {
  return apiPost("api/events/delete_event.php", {
    id: id
  });
}

async function adminGetApplications(params = {}) {
  const qs = new URLSearchParams(params).toString();
  return apiGet("api/membership/get_all_applications.php" + (qs ? "?" + qs : ""));
}

async function adminReviewApplication(data) {
  return apiPost("api/membership/review_application.php", data);
}

async function adminGetOrientationRegs(params = {}) {
  const qs = new URLSearchParams(params).toString();
  return apiGet("api/membership/get_orientation_registrations.php" + (qs ? "?" + qs : ""));
}

async function adminCompleteOrientation(data) {
  return apiPost("api/membership/complete_orientation.php", data);
}

async function adminGetSchedules() {
  return apiGet("api/membership/manage_schedules.php");
}

async function adminCreateSchedule(data) {
  return apiPost("api/membership/manage_schedules.php", data);
}

async function adminGetLoans(params = {}) {
  const qs = new URLSearchParams(params).toString();
  return apiGet("api/transactions/get_loans.php" + (qs ? "?" + qs : ""));
}

async function adminReviewLoan(data) {
  return apiPost("api/transactions/review_loan.php", data);
}

async function adminGetWithdrawals(params = {}) {
  const qs = new URLSearchParams(params).toString();
  return apiGet("api/transactions/get_withdrawals.php" + (qs ? "?" + qs : ""));
}

async function adminReviewWithdrawal(data) {
  return apiPost("api/transactions/review_withdrawal.php", data);
}

async function adminGetAnnouncements() {
  return apiGet("api/announcements/get_announcements.php");
}

async function adminCreateAnnouncement(data) {
  return apiPost("api/announcements/create_announcement.php", data);
}

async function adminDeleteAnnouncement(id) {
  return apiPost("api/announcements/delete_announcement.php", {
    id: id
  });
}

async function adminGetBenefits() {
  return apiGet("api/benefits/get_benefits.php");
}

async function adminCreateBenefit(data) {
  return apiPost("api/benefits/create_benefit.php", data);
}

async function adminUpdateBenefit(data) {
  return apiPost("api/benefits/update_benefit.php", data);
}

async function adminDeleteBenefit(id) {
  return apiPost("api/benefits/delete_benefit.php", {
    id: id
  });
}

async function adminGetAnalytics() {
  return apiGet("api/analytics/get_analytics.php");
}

function adminConfirm(message, onConfirm) {
  const bg = document.getElementById("confirm-bg");
  const msg = document.getElementById("confirm-msg");
  const btn = document.getElementById("confirm-ok-btn");
  if (!bg || !msg || !btn) return;
  msg.textContent = message;
  btn.onclick = () => {
    closeAdminConfirm();
    onConfirm();
  };
  bg.classList.add("open");
}

function closeAdminConfirm() {
  const bg = document.getElementById("confirm-bg");
  if (bg) bg.classList.remove("open");
}

function adminSearch(inputId, rowsSelector) {
  const query = document.getElementById(inputId)?.value?.toLowerCase() || "";
  document.querySelectorAll(rowsSelector).forEach(row => {
    row.style.display = row.textContent.toLowerCase().includes(query) ? "" : "none";
  });
}

function renderMiniBarChart(containerId, data, maxVal, color) {
  const container = document.getElementById(containerId);
  if (!container) return;
  const max = maxVal || Math.max(...data.map(d => d.value), 1);
  container.innerHTML = data.map(d => `\n        <div class="mini-bar-group">\n            <div class="mini-bar" style="height:${Math.round(d.value / max * 60)}px;\n                                         background:${color || "var(--gold)"};"\n                 title="${d.label}: ${d.value}"></div>\n            <div class="mini-bar-label">${d.label}</div>\n        </div>\n    `).join("");
}