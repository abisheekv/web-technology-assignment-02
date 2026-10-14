const form = document.getElementById("registerForm");
const successBox = document.getElementById("successBox");

const patterns = {
  name:   /^[A-Za-z][A-Za-z .]{2,49}$/,
  email:  /^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/,
  mobile: /^[6-9][0-9]{9}$/,
  pwd:    /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/
};

function showError(fieldId, message) {
  const el = document.getElementById(fieldId);
  el.classList.remove("has-ok");
  el.classList.add("has-error");
  document.getElementById(fieldId + "Err").textContent = message;
}

function showOk(fieldId) {
  const el = document.getElementById(fieldId);
  el.classList.remove("has-error");
  el.classList.add("has-ok");
  document.getElementById(fieldId + "Err").textContent = "";
}

function checkName() {
  const v = document.getElementById("fullName").value.trim();
  if (v === "") { showError("fullName", "Full Name cannot be empty."); return false; }
  if (!patterns.name.test(v)) { showError("fullName", "Use letters only (3-50 characters)."); return false; }
  showOk("fullName"); return true;
}

function checkEmail() {
  const v = document.getElementById("emailAddr").value.trim();
  if (v === "") { showError("emailAddr", "Email Address cannot be empty."); return false; }
  if (!patterns.email.test(v)) { showError("emailAddr", "Enter a valid email address."); return false; }
  showOk("emailAddr"); return true;
}

function checkMobile() {
  const v = document.getElementById("mobileNo").value.trim();
  if (v === "") { showError("mobileNo", "Mobile number cannot be empty."); return false; }
  if (!/^[0-9]+$/.test(v) || v.length !== 10) { showError("mobileNo", "Mobile number must contain exactly 10 digits."); return false; }
  if (!patterns.mobile.test(v)) { showError("mobileNo", "Mobile number must start with 6, 7, 8 or 9."); return false; }
  showOk("mobileNo"); return true;
}

function checkPassword() {
  const v = document.getElementById("pwd").value;
  if (v === "") { showError("pwd", "Password cannot be empty."); return false; }
  if (!patterns.pwd.test(v)) { showError("pwd", "Min 8 chars with uppercase, lowercase, digit & symbol."); return false; }
  showOk("pwd"); return true;
}

function checkConfirm() {
  const a = document.getElementById("pwd").value;
  const b = document.getElementById("pwd2").value;
  if (b === "") { showError("pwd2", "Please confirm your password."); return false; }
  if (a !== b) { showError("pwd2", "Passwords do not match."); return false; }
  showOk("pwd2"); return true;
}

function checkGender() {
  const group = document.getElementById("genderGroup");
  const chosen = document.querySelector('input[name="gender"]:checked');
  if (!chosen) {
    group.classList.add("has-error");
    document.getElementById("genderErr").textContent = "Please select your gender.";
    return false;
  }
  group.classList.remove("has-error");
  document.getElementById("genderErr").textContent = "";
  return true;
}

function checkDob() {
  const v = document.getElementById("dob").value;
  if (v === "") { showError("dob", "Date of birth is required."); return false; }
  const born = new Date(v);
  if (isNaN(born.getTime())) { showError("dob", "Enter a valid date."); return false; }
  const today = new Date();
  let age = today.getFullYear() - born.getFullYear();
  const monthGap = today.getMonth() - born.getMonth();
  if (monthGap < 0 || (monthGap === 0 && today.getDate() < born.getDate())) age--;
  if (age < 18) { showError("dob", "You must be at least 18 years old."); return false; }
  showOk("dob"); return true;
}

function checkCity() {
  if (document.getElementById("cityList").value === "") {
    showError("cityList", "Please select a city."); return false;
  }
  showOk("cityList"); return true;
}

function checkTerms() {
  const box = document.getElementById("agreeBox");
  if (!box.checked) {
    document.getElementById("agreeBoxErr").textContent = "You must accept the Terms & Conditions.";
    return false;
  }
  document.getElementById("agreeBoxErr").textContent = "";
  return true;
}

form.addEventListener("submit", function (event) {
  event.preventDefault();
  const results = [checkName(), checkEmail(), checkMobile(), checkGender(),
                   checkPassword(), checkConfirm(), checkDob(), checkCity(), checkTerms()];
  if (results.includes(false)) return;

  const name = document.getElementById("fullName").value.trim();
  document.getElementById("welcomeLine").textContent = "Welcome, " + name + ".";
  form.style.display = "none";
  document.querySelector(".sub-text").style.display = "none";
  successBox.style.display = "block";
});

// real-time validation
document.getElementById("fullName").addEventListener("input", checkName);
document.getElementById("emailAddr").addEventListener("input", checkEmail);
document.getElementById("mobileNo").addEventListener("input", checkMobile);
document.getElementById("pwd").addEventListener("input", checkPassword);
document.getElementById("pwd2").addEventListener("input", checkConfirm);
document.getElementById("cityList").addEventListener("change", checkCity);
document.getElementById("dob").addEventListener("change", checkDob);
document.querySelectorAll('input[name="gender"]').forEach(r => r.addEventListener("change", checkGender));

// reset clears values, messages and styles
document.getElementById("resetBtn").addEventListener("click", function () {
  setTimeout(function () {
    document.querySelectorAll(".err-text").forEach(s => (s.textContent = ""));
    document.querySelectorAll(".has-error, .has-ok").forEach(e => e.classList.remove("has-error", "has-ok"));
    successBox.style.display = "none";
  }, 0);
});
