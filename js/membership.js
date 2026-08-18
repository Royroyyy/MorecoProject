async function getMyApplication() {
  return apiGet("api/membership/get_application.php");
}

async function getOrientationSchedules() {
  return apiGet("api/membership/get_orientations.php");
}

async function selectOrientation(scheduleId) {
  return apiPost("api/membership/select_orientation.php", {
    schedule_id: scheduleId
  });
}

function renderMembershipSteps(currentStatus, orientationStatus) {
  const steps = [ {
    key: "registered",
    label: "Registered"
  }, {
    key: "applied",
    label: "Applied"
  }, {
    key: "approved",
    label: "Approved"
  }, {
    key: "orientation",
    label: "Orientation"
  }, {
    key: "member",
    label: "Member"
  } ];
  const statusMap = {
    applicant_no_app: 0,
    applicant_pending: 1,
    applicant_review: 1,
    approved: 2,
    orientation_set: 3,
    member: 4
  };
  const currentStep = statusMap[currentStatus] ?? 0;
  return `\n        <div class="status-steps">\n            ${steps.map((step, i) => `\n                <div class="status-step ${i < currentStep ? "done" : ""} ${i === currentStep ? "active" : ""}">\n                    <div class="step-dot">${i < currentStep ? "✓" : i + 1}</div>\n                    <span>${step.label}</span>\n                </div>\n                ${i < steps.length - 1 ? '<div class="step-line"></div>' : ""}\n            `).join("")}\n        </div>\n    `;
}

function renderApplicationStatus(app) {
  if (!app) {
    return `\n            <div class="application-status-card no-app">\n                <div class="app-status-icon">📋</div>\n                <h3>No Application Yet</h3>\n                <p>You have not submitted a membership application. Apply now to become a MORECO member.</p>\n                <a href="apply.html" class="btn btn-primary btn-lg" style="margin-top:1rem;">\n                    Apply for Membership\n                </a>\n            </div>\n        `;
  }
  const statusColors = {
    pending: "#f59e0b",
    under_review: "#3b82f6",
    approved: "#22c55e",
    rejected: "#ef4444"
  };
  const statusIcons = {
    pending: "⏳",
    under_review: "🔍",
    approved: "✅",
    rejected: "❌"
  };
  const color = statusColors[app.status] || "#94a3b8";
  const icon = statusIcons[app.status] || "📋";
  return `\n        <div class="application-status-card" style="border-left: 4px solid ${color};">\n            <div style="display:flex; align-items:center; gap:1rem; margin-bottom:1rem;">\n                <div style="font-size:2rem;">${icon}</div>\n                <div>\n                    <div style="font-weight:700; font-size:1rem;">Membership Application</div>\n                    <div>${statusBadge(app.status)}</div>\n                </div>\n                <div style="margin-left:auto; font-size:0.78rem; color:var(--text-muted);">\n                    Submitted: ${formatDate(app.created_at)}\n                </div>\n            </div>\n\n            ${app.status === "approved" ? `\n                <div style="background:rgba(34,197,94,0.1); border:1px solid rgba(34,197,94,0.3);\n                            border-radius:8px; padding:1rem; margin-bottom:1rem;">\n                    <strong style="color:#22c55e;">🎉 Application Approved!</strong>\n                    <p style="font-size:0.85rem; color:var(--text-muted); margin-top:0.3rem;">\n                        Please select an orientation schedule below to complete your membership.\n                    </p>\n                </div>\n            ` : ""}\n\n            ${app.status === "rejected" ? `\n                <div style="background:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.3);\n                            border-radius:8px; padding:1rem; margin-bottom:1rem;">\n                    <strong style="color:#ef4444;">Application Not Approved</strong>\n                    ${app.rejection_reason ? `\n                        <p style="font-size:0.85rem; color:var(--text-muted); margin-top:0.3rem;">\n                            Reason: ${app.rejection_reason}\n                        </p>\n                    ` : ""}\n                </div>\n            ` : ""}\n\n            ${app.status === "pending" || app.status === "under_review" ? `\n                <p style="font-size:0.85rem; color:var(--text-muted);">\n                    Your application is currently being reviewed by our team.\n                    This typically takes 3–5 business days. You will be notified once a decision is made.\n                </p>\n            ` : ""}\n        </div>\n    `;
}

function renderOrientationCards(schedules, myReg, onSelect) {
  if (myReg) {
    return `\n            <div class="orientation-confirmed">\n                <div style="font-size:2rem; margin-bottom:0.75rem;">📅</div>\n                <h3>Orientation Scheduled</h3>\n                <div style="margin:1rem 0; padding:1rem; background:rgba(240,180,41,0.08);\n                            border:1px solid rgba(240,180,41,0.25); border-radius:10px;">\n                    <div style="font-weight:700; margin-bottom:0.5rem;">${myReg.title}</div>\n                    <div style="font-size:0.87rem; color:var(--text-muted);">\n                        📅 ${formatDate(myReg.scheduled_date)}<br>\n                        🕐 ${myReg.scheduled_time}<br>\n                        📍 ${myReg.location}\n                    </div>\n                </div>\n                <p style="font-size:0.85rem; color:var(--text-muted);">\n                    Please attend on the scheduled date. Your membership will be activated after completion.\n                </p>\n                ${statusBadge(myReg.status)}\n            </div>\n        `;
  }
  if (!schedules || !schedules.length) {
    return emptyState("📅", "No orientation schedules available right now. Please check back later or contact the MORECO office.");
  }
  return `\n        <div class="cards-grid">\n            ${schedules.map(s => `\n                <div class="card orientation-card">\n                    <div class="card-body">\n                        <div class="card-tag">Orientation</div>\n                        <h3>${s.title}</h3>\n                        <div class="card-meta" style="flex-direction:column; align-items:flex-start; gap:0.35rem;">\n                            <span>📅 ${formatDate(s.scheduled_date)}</span>\n                            <span>🕐 ${s.scheduled_time}</span>\n                            <span>📍 ${s.location}</span>\n                            <span>👥 ${s.slots_remaining} slot${s.slots_remaining !== 1 ? "s" : ""} remaining</span>\n                        </div>\n                        <button class="btn btn-primary btn-full" style="margin-top:1rem;"\n                                onclick="${onSelect}(${s.id})">\n                            Select This Schedule\n                        </button>\n                    </div>\n                </div>\n            `).join("")}\n        </div>\n    `;
}