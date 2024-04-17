document.addEventListener("DOMContentLoaded", function () {

    document.getElementById("progress").innerHTML = '<div style="width:' + percent + '%; background-color: lightblue; height: 19px;">&nbsp;' + percent + '%</div>';

    let progressContainer = document.getElementById("progress");
    let innerProgress = document.querySelector(".inner");

    function updateProgressBar(progress) {
        innerProgress.style.width = progress + "%";
    }

    function simulateProgress() {
        let progress = 0;
        let interval = setInterval(function () {
            progress += 10;
            if (progress <= 100) {
                updateProgressBar(progress);
            } else {
                clearInterval(interval);
            }
        }, 1000);
    }

    simulateProgress();
});

