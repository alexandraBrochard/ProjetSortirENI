
    function updateClock() {
    let now = new Date();
    let clock = document.getElementById('clock');
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: 'numeric', minute: 'numeric', second: 'numeric', hour12: false };
    clock.innerHTML = now.toLocaleString('fr-FR',options);

}
    setInterval(updateClock, 1000);
