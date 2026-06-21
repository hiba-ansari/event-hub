function copyEventLink(filename) {
    let link = "https://titan.csit.rmit.edu.au/~s4100892/group9/web-studio-project-group_09_wps_2024" + filename;
    let betterLink = link.replace("2024..", "2024");
    navigator.clipboard.writeText(betterLink);
}

function changeButtonText(button) {
    if (button.innerHTML=="Share") {
        button.innerHTML = "Copied!";
        button.style.left = "68%";

        setTimeout(()=> {
            button.innerHTML = "Share";
            button.style.left = "69%";
        }, 2000);
    }
}

function saveEvent(button) {
    if (button.innerHTML=="+") {
        button.innerHTML = "&#10003;";
        button.style.backgroundColor = "#0D99FF";
        button.style.color = "white";
    }
    else {
        button.innerHTML = "+"
        button.style.backgroundColor = "var(--event-button-color)";
        button.style.color = "black";
    }
}

let page = 1;
let maxPages = 5;
const pageNo = document.getElementById("page-no");
const pageInput = document.getElementById("page-input");
const pageForm = document.getElementById("page-form");

function changePage() {
    pageNo.innerHTML = page;
    pageInput.value = page;
    pageForm.submit();
}

function nextPage() {
    if (page < maxPages) {
        page++;
        changePage();
    }
}

function prevPage() {
    if (page > 1) {
        page--;
        changePage();
    }
}