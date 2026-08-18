async function loginUser(username, password) {
  return apiPost("api/auth/login.php", {
    username: username,
    password: password
  });
}

async function registerUser(data) {
  return apiPost("api/auth/register.php", data);
}

async function logoutUser() {
  await fetch("api/auth/logout.php", {
    method: "POST"
  });
  clearSession();
}

function requireLoginRedirect(returnTo) {
  if (!isLoggedIn()) {
    window.location.href = `login.html?returnTo=${returnTo || window.location.pathname}`;
  }
}

function redirectIfLoggedIn(destination) {
  if (isLoggedIn()) {
    window.location.href = destination || "index.html";
  }
}

function cacheUser(user) {
  sessionStorage.setItem("user", JSON.stringify(user));
}

function getCurrentUser() {
  return JSON.parse(sessionStorage.getItem("user"));
}

function isLoggedIn() {
  return !!sessionStorage.getItem("user");
}

function clearSession() {
  sessionStorage.removeItem("user");
}