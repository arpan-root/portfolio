const loginText = document.querySelector(".title-text .login");
    const loginForm = document.querySelector("form.login");
    const loginBtn = document.querySelector("label.login");
    const signupBtn = document.querySelector("label.signup");
    const signupLink = document.querySelector("form .signup-link a");
    signupBtn.onclick = (()=>{
      loginForm.style.marginLeft = "-50%";
      loginText.style.marginLeft = "-50%";
    });
    loginBtn.onclick = (()=>{
      loginForm.style.marginLeft = "0%";
      loginText.style.marginLeft = "0%";
    });
    signupLink.onclick = (()=>{
      signupBtn.click();
      return false;
    });

    document.addEventListener('DOMContentLoaded', function() {
      // Check for saved email and password
      const savedEmail = localStorage.getItem('savedEmail');
      const savedPassword = localStorage.getItem('savedPassword');
      
      // Populate email field if saved
      if (savedEmail) {
          document.getElementById('email').value = savedEmail;
      }

      // Populate password field if saved and email field is already filled
      if (savedEmail && savedPassword && document.getElementById('email').value) {
          document.getElementById('password').value = savedPassword;
      }

      // Handle form submission
      document.getElementById('loginForm').addEventListener('submit', function(event) {
          // If "Remember me" is checked, save email and password to local storage
          if (document.getElementById('remember_me').checked) {
              localStorage.setItem('savedEmail', document.getElementById('email').value);
              localStorage.setItem('savedPassword', document.getElementById('password').value);
          } else {
              // If "Remember me" is not checked, remove saved email and password
              localStorage.removeItem('savedEmail');
              localStorage.removeItem('savedPassword');
          }
      });
  });
  document.addEventListener('keydown', function(event) {
    const SCROLL_AMOUNT = 50; // Change this value to adjust the scroll amount

    switch (event.key) {
        case "ArrowUp":
            window.scrollBy(0, -SCROLL_AMOUNT);
            break;
        case "ArrowDown":
            window.scrollBy(0, SCROLL_AMOUNT);
            break;
        case "ArrowLeft":
            window.scrollBy(-SCROLL_AMOUNT, 0);
            break;
        case "ArrowRight":
            window.scrollBy(SCROLL_AMOUNT, 0);
            break;
        default:
            break;
    }
});
window.onload = function() {
    // Check if cookies exist and autofill the form
    var email = getCookie('email');
    var password = getCookie('password');
    if (email && password) {
        document.getElementById('email').value = email;
        document.getElementById('password').value = password;
        document.getElementById('remember_me').checked = true;
    }
}

function getCookie(name) {
    var cookieArr = document.cookie.split(";");
    for (var i = 0; i < cookieArr.length; i++) {
        var cookiePair = cookieArr[i].split("=");
        if (name == cookiePair[0].trim()) {
            return decodeURIComponent(cookiePair[1]);
        }
    }
    return null;
}
