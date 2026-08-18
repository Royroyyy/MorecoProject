async function loadEvents(params = {}) {
  const qs = new URLSearchParams(params).toString();
  return apiGet("api/events/get_events.php" + (qs ? "?" + qs : ""));
}

async function createEvent(data) {
  return apiPost("api/events/create_event.php", data);
}

async function updateEvent(data) {
  return apiPost("api/events/update_event.php", data);
}

async function deleteEvent(id) {
  return apiPost("api/events/delete_event.php", {
    id: id
  });
}

async function registerForEvent(eventId) {
  return apiPost("api/registrations/register_event.php", {
    event_id: eventId
  });
}

async function getMyRegistrations() {
  return apiGet("api/registrations/get_registrations.php");
}

async function cancelRegistration(id) {
  return apiPost("api/registrations/cancel_registration.php", {
    id: id
  });
}

function isRegistered(eventId, myRegs) {
  if (!myRegs || !myRegs.length) return false;
  return myRegs.some(r => parseInt(r.event_id) === parseInt(eventId));
}

function getRegistrationId(eventId, myRegs) {
  const reg = myRegs.find(r => parseInt(r.event_id) === parseInt(eventId));
  return reg ? reg.id : null;
}

function slotsLeft(event) {
  return parseInt(event.slots) - parseInt(event.registration_count || 0);
}

function renderEventCard(ev, myRegs, onClickFn) {
  const registered = isRegistered(ev.id, myRegs);
  const left = slotsLeft(ev);
  const full = left <= 0;
  const imgSrc = ev.image_url || "";
  return `\n        <div class="card event-card" onclick="${onClickFn}(${ev.id})">\n            <div class="card-img">\n                ${imgSrc ? `<img src="${imgSrc}" alt="${ev.title}"\n                            onerror="this.parentNode.innerHTML='<span class=card-emoji>${ev.emoji || "📅"}</span>'">` : `<span class="card-emoji">${ev.emoji || "📅"}</span>`}\n                <div class="card-overlay">\n                    <div class="btn btn-primary btn-sm">\n                        ${registered ? "✅ Registered" : "👆 View Details"}\n                    </div>\n                </div>\n                ${ev.status === "completed" ? '<div class="card-completed-badge">Completed</div>' : ""}\n            </div>\n            <div class="card-body">\n                <div class="card-tag">${ev.category}</div>\n                <h3>${ev.title}</h3>\n                <p>${(ev.description || "").substring(0, 90)}${ev.description && ev.description.length > 90 ? "…" : ""}</p>\n                <div class="card-meta">\n                    <span>📅 ${formatDate(ev.event_date)}</span>\n                    ${registered ? '<span style="margin-left:auto;color:#22c55e;font-size:0.75rem;font-weight:700;">✓ Registered</span>' : full && ev.status === "upcoming" ? '<span style="margin-left:auto;color:#ef4444;font-size:0.75rem;font-weight:700;">Full</span>' : ""}\n                </div>\n            </div>\n        </div>\n    `;
}

const MONTHS = [ "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December" ];

function getEventsForDay(events, year, month, day) {
  return events.filter(e => {
    const [ey, em, ed] = (e.event_date || "").split("-").map(Number);
    return ey === year && em - 1 === month && ed === day;
  });
}

function buildCalendarGrid(containerId, labelId, events, myRegs, year, month, onEventClick) {
  const today = new Date;
  const label = document.getElementById(labelId);
  const container = document.getElementById(containerId);
  if (!label || !container) return;
  label.textContent = `${MONTHS[month]} ${year}`;
  container.innerHTML = "";
  const firstDay = new Date(year, month, 1).getDay();
  const daysInMonth = new Date(year, month + 1, 0).getDate();
  for (let i = 0; i < firstDay; i++) {
    const empty = document.createElement("div");
    empty.className = "cal-day empty";
    container.appendChild(empty);
  }
  for (let d = 1; d <= daysInMonth; d++) {
    const cell = document.createElement("div");
    const isToday = d === today.getDate() && month === today.getMonth() && year === today.getFullYear();
    cell.className = "cal-day" + (isToday ? " today" : "");
    const numEl = document.createElement("div");
    numEl.className = "day-num";
    numEl.textContent = d;
    cell.appendChild(numEl);
    getEventsForDay(events, year, month, d).forEach(ev => {
      const pill = document.createElement("div");
      const reg = isRegistered(ev.id, myRegs);
      pill.className = "cal-event-pill" + (reg ? " registered" : "");
      pill.textContent = ev.title;
      pill.title = `${ev.title} — ${ev.location || ""}`;
      pill.onclick = e => {
        e.stopPropagation();
        onEventClick(ev.id);
      };
      cell.appendChild(pill);
    });
    container.appendChild(cell);
  }
}