import { f as e, h as r } from "./utils-0K0NRc03.js";

/* empty css */

document.addEventListener("DOMContentLoaded", () => {
  e();
  r("forgot-form", "../api/forgot-password.php", "error-message", (o) => {
    alert("Link reset password telah dikirim ke email terdaftar. Silakan cek email Anda.");
  });
});
