(() => {
    const password = document.getElementById("password");
    const toggle = document.getElementById("pwToggle");

    if (!password || !toggle) return;

    const showPassword = () => {
        password.type = "text";
    };

    const hidePassword = () => {
        password.type = "password";
    };

    // Mouse
    toggle.addEventListener("mousedown", showPassword);
    toggle.addEventListener("mouseup", hidePassword);
    toggle.addEventListener("mouseleave", hidePassword);

    // Touch (mobil)
    toggle.addEventListener("touchstart", (e) => {
        e.preventDefault();
        showPassword();
    }, { passive: false });

    toggle.addEventListener("touchend", hidePassword);
    toggle.addEventListener("touchcancel", hidePassword);
})();
