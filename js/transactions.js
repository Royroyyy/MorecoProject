async function applyLoan(data) {
  return apiPost("api/transactions/apply_loan.php", data);
}

async function getLoans(params = {}) {
  const qs = new URLSearchParams(params).toString();
  return apiGet("api/transactions/get_loans.php" + (qs ? "?" + qs : ""));
}

async function reviewLoan(data) {
  return apiPost("api/transactions/review_loan.php", data);
}

async function requestWithdrawal(data) {
  return apiPost("api/transactions/request_withdrawal.php", data);
}

async function getWithdrawals(params = {}) {
  const qs = new URLSearchParams(params).toString();
  return apiGet("api/transactions/get_withdrawals.php" + (qs ? "?" + qs : ""));
}

async function reviewWithdrawal(data) {
  return apiPost("api/transactions/review_withdrawal.php", data);
}

async function scanQR(token) {
  return apiPost("api/transactions/scan_qr.php", {
    token: token
  });
}

function generateQRCode(containerId, token, size = 200) {
  const container = document.getElementById(containerId);
  if (!container) return;
  container.innerHTML = "";
  if (typeof QRCode === "undefined") {
    container.innerHTML = `\n            <div style="padding:1rem;text-align:center;color:var(--text-muted);font-size:0.83rem;">\n                QR library not loaded. Token: <code style="font-size:0.75rem;word-break:break-all;">${token}</code>\n            </div>`;
    return;
  }
  new QRCode(container, {
    text: token,
    width: size,
    height: size,
    colorDark: "#000000",
    colorLight: "#ffffff",
    correctLevel: QRCode.CorrectLevel.H
  });
}

function calculateMonthlyPayment(principal, interestRatePercent, termMonths) {
  const r = interestRatePercent / 100;
  const monthly = principal * (r * Math.pow(1 + r, termMonths)) / (Math.pow(1 + r, termMonths) - 1);
  return isFinite(monthly) ? monthly : principal / termMonths;
}

function calculateTotalPayment(principal, interestRatePercent, termMonths) {
  return calculateMonthlyPayment(principal, interestRatePercent, termMonths) * termMonths;
}

function renderLoanCard(loan, showQR = true) {
  const isApproved = loan.status === "approved";
  const isReleased = loan.status === "released";
  const hasFunds = isApproved || isReleased;
  return `\n        <div class="transaction-card" id="loan-${loan.id}">\n            <div class="txn-card-header">\n                <div>\n                    <div style="font-weight:700; font-size:0.95rem;">\n                        💰 Loan — ${formatMoney(loan.amount)}\n                    </div>\n                    <div style="font-size:0.78rem; color:var(--text-muted); margin-top:3px;">\n                        Applied: ${formatDateTime(loan.created_at)}\n                    </div>\n                </div>\n                ${statusBadge(loan.status)}\n            </div>\n            <div class="txn-card-body">\n                <div class="txn-detail-grid">\n                    <div class="txn-detail"><label>Purpose</label><span>${loan.purpose || "—"}</span></div>\n                    <div class="txn-detail"><label>Term</label><span>${loan.term_months} months</span></div>\n                    <div class="txn-detail"><label>Interest</label><span>${loan.interest_rate}% / month</span></div>\n                    <div class="txn-detail"><label>Monthly Payment</label>\n                        <span>${formatMoney(calculateMonthlyPayment(loan.amount, loan.interest_rate, loan.term_months))}</span>\n                    </div>\n                    ${loan.due_date ? `<div class="txn-detail"><label>Due Date</label><span>${formatDate(loan.due_date)}</span></div>` : ""}\n                    ${loan.rejection_reason ? `<div class="txn-detail" style="grid-column:1/-1;"><label>Rejection Reason</label><span style="color:#fca5a5;">${loan.rejection_reason}</span></div>` : ""}\n                </div>\n\n                ${hasFunds && loan.qr && showQR ? `\n                    <div class="qr-section">\n                        <div class="qr-label">\n                            ${isReleased ? '<span style="color:#22c55e;font-weight:700;">✅ Funds Released</span>' : '<span style="color:var(--gold);font-weight:700;">📱 Present this QR to the clerk</span>'}\n                        </div>\n                        ${!isReleased && loan.qr.qr_status === "active" ? `\n                            <div id="qr-loan-${loan.id}" class="qr-container"></div>\n                            <div style="font-size:0.72rem;color:var(--text-muted);margin-top:0.5rem;text-align:center;">\n                                Valid until: ${formatDateTime(loan.qr.expires_at || "")}\n                            </div>\n                        ` : ""}\n                        ${loan.qr.qr_status === "scanned" ? `\n                            <div style="text-align:center;color:#22c55e;font-size:0.85rem;padding:0.75rem;">\n                                ✅ QR code already scanned. Funds released.\n                            </div>\n                        ` : ""}\n                        ${loan.qr.qr_status === "expired" ? `\n                            <div style="text-align:center;color:#ef4444;font-size:0.85rem;padding:0.75rem;">\n                                ⏰ QR code expired. Contact MORECO office.\n                            </div>\n                        ` : ""}\n                    </div>\n                ` : ""}\n            </div>\n        </div>\n    `;
}

function renderWithdrawalCard(w, showQR = true) {
  const isApproved = w.status === "approved";
  const isReleased = w.status === "released";
  const hasFunds = isApproved || isReleased;
  return `\n        <div class="transaction-card" id="withdrawal-${w.id}">\n            <div class="txn-card-header">\n                <div>\n                    <div style="font-weight:700; font-size:0.95rem;">\n                        💸 Withdrawal — ${formatMoney(w.amount)}\n                    </div>\n                    <div style="font-size:0.78rem; color:var(--text-muted); margin-top:3px;">\n                        Requested: ${formatDateTime(w.created_at)}\n                    </div>\n                </div>\n                ${statusBadge(w.status)}\n            </div>\n            <div class="txn-card-body">\n                <div class="txn-detail-grid">\n                    <div class="txn-detail"><label>Account Number</label><span>${w.account_number}</span></div>\n                    <div class="txn-detail"><label>Account Name</label><span>${w.account_name || "—"}</span></div>\n                    ${w.notes ? `<div class="txn-detail"><label>Notes</label><span>${w.notes}</span></div>` : ""}\n                    ${w.rejection_reason ? `<div class="txn-detail" style="grid-column:1/-1;"><label>Rejection Reason</label><span style="color:#fca5a5;">${w.rejection_reason}</span></div>` : ""}\n                    ${isReleased && w.released_at ? `<div class="txn-detail"><label>Released At</label><span>${formatDateTime(w.released_at)}</span></div>` : ""}\n                </div>\n\n                ${hasFunds && w.qr && showQR ? `\n                    <div class="qr-section">\n                        <div class="qr-label">\n                            ${isReleased ? '<span style="color:#22c55e;font-weight:700;">✅ Funds Released</span>' : '<span style="color:var(--gold);font-weight:700;">📱 Present this QR to the clerk</span>'}\n                        </div>\n                        ${!isReleased && w.qr.qr_status === "active" ? `\n                            <div id="qr-withdrawal-${w.id}" class="qr-container"></div>\n                            <div style="font-size:0.72rem;color:var(--text-muted);margin-top:0.5rem;text-align:center;">\n                                Valid for 3 days from approval\n                            </div>\n                        ` : ""}\n                        ${w.qr.qr_status === "scanned" ? `\n                            <div style="text-align:center;color:#22c55e;font-size:0.85rem;padding:0.75rem;">\n                                ✅ QR code already scanned. Funds released.\n                            </div>\n                        ` : ""}\n                        ${w.qr.qr_status === "expired" ? `\n                            <div style="text-align:center;color:#ef4444;font-size:0.85rem;padding:0.75rem;">\n                                ⏰ QR code expired. Please submit a new withdrawal request.\n                            </div>\n                        ` : ""}\n                    </div>\n                ` : ""}\n            </div>\n        </div>\n    `;
}

function renderAllQRCodes(loans, withdrawals) {
  (loans || []).forEach(loan => {
    if (loan.qr?.qr_status === "active" && loan.status === "approved") {
      generateQRCode(`qr-loan-${loan.id}`, loan.qr.qr_token, 180);
    }
  });
  (withdrawals || []).forEach(w => {
    if (w.qr?.qr_status === "active" && w.status === "approved") {
      generateQRCode(`qr-withdrawal-${w.id}`, w.qr.qr_token, 180);
    }
  });
}

document.addEventListener("DOMContentLoaded", () => {
  const user = JSON.parse(sessionStorage.getItem("user"));
  if (user && (user.role === "clerk" || user.role === "admin")) {
    const scannerPanel = document.getElementById("scanner-panel");
    if (scannerPanel) {
      scannerPanel.style.display = "block";
    }
  }
});