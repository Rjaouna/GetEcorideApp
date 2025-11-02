class AudioPlayer {
    constructor(src) {
        this.audio = new Audio(src);
    }

    play() {
        this.audio.play().catch((err) => {
            console.warn("Lecture bloquée par le navigateur :", err);
        });
    }


    stop() {
        this.audio.pause();
        this.audio.currentTime = 0;
    }


}
const button = document.getElementById("join-carpooling-btn");
button.addEventListener("click", async () => {
    const player = new Audio("/assets/media/light-switch-382712.mp3");
    player.play();
});