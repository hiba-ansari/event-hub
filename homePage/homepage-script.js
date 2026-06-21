let currentSlide = 0;
const slides = document.querySelectorAll('.carousel-slide');
const trendingSlides = document.querySelectorAll('.trending-carousel-slide');
let i = 1;

function showSlide(index) {
    trendingSlides.forEach((slide, i) => {
        slide.style.display = (i === index) ? 'block' : 'none';
    });
}

function nextSlide() {
    currentSlide = (currentSlide + 1) % trendingSlides.length;
    showSlide(currentSlide);
}

function prevSlide() {
    currentSlide = (currentSlide - 1 + trendingSlides.length) % trendingSlides.length;
    showSlide(currentSlide);
}

// Initialize the carousel
showSlide(currentSlide);

//carousel slideshow
let slideIndex = 0;
trendingSlideShow();

function trendingSlideShow() {
    let i;
    let slides = trendingSlides;
    for (i = 0; i < slides.length; i++) {
        slides[i].style.display = "none";
    }
    slideIndex++;
    
    if (slideIndex > slides.length) {
        slideIndex = 1;
    }

    slides[slideIndex-1].style.display = "block";

    setTimeout(trendingSlideShow, 3000);
}

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