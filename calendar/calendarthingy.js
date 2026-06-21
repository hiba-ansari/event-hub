
// function scrollToLayer(layerIndex) {
//     if (layerIndex >= 0 && layerIndex < layers.length) {
//         layers[layerIndex].scrollIntoView({ behavior: 'smooth' });
//     }
// }

// window.addEventListener('wheel', (event) => {
//     if (event.deltaY > 0 && currentLayer < layers.length - 1) {
//         currentLayer++;
//     } else if (event.deltaY < 0 && currentLayer > 0) {
//         currentLayer--;
//     }
//     scrollToLayer(currentLayer);
//     event.preventDefault();
// });

const daysContainer = document.getElementById('daysContainer');
const monthYear = document.getElementById('monthYear');
const eventsContainer = document.getElementById('listed-events-container');
const prevMonthButton = document.getElementById('prevMonth');
const nextMonthButton = document.getElementById('nextMonth');

let currentDate = new Date();

function renderCalendar() {
    daysContainer.innerHTML = '';
    monthYear.innerText = currentDate.toLocaleString('default', { month: 'long', year: 'numeric' });

    const totalDays = new Date(currentDate.getFullYear(), currentDate.getMonth() + 1, 0).getDate();
    let startDay = new Date(currentDate.getFullYear(), currentDate.getMonth(), 1).getDay();
    startDay = (startDay + 6) % 7;

    for (let i = 0; i < startDay; i++) {
        const emptyDay = document.createElement('div');
        emptyDay.className = 'day';
        emptyDay.className = 'emptyDay';
        daysContainer.appendChild(emptyDay);
    }

    for (let day = 1; day <= totalDays; day++) {
        const dayElement = document.createElement('div');
        dayElement.className = 'day';

        const dayNumber = document.createElement('span');
        dayNumber.className = 'day-number';
        dayNumber.innerText = day;

        const dayOfWeek = (startDay + day) % 7;
        if (dayOfWeek === 6 || dayOfWeek === 0) {
            dayElement.style.backgroundColor = "#fcedd8";
        }

        if (day === new Date().getDate() && currentDate.getMonth() === new Date().getMonth() && currentDate.getFullYear() === new Date().getFullYear()) {
            dayElement.style.backgroundColor = "var(--OURorang1)";
        }

        eventsData.forEach(events => {
            const eventDate = new Date(events.EventDate);

            if (day === eventDate.getDate() && currentDate.getMonth() === eventDate.getMonth() && currentDate.getFullYear() === eventDate.getFullYear()) {
                const event = document.createElement('li');
                event.className = 'event';
                event.innerText = events.EventName;
                dayElement.appendChild(event);
            }
            
        });

        dayElement.appendChild(dayNumber);
        daysContainer.appendChild(dayElement);
        
    }
    const emptyDaysToFill = 42 - (startDay + totalDays);
    for (let i = 0; i < emptyDaysToFill; i++) {
        const emptyDay = document.createElement('div');
        emptyDay.className = 'emptyDay';
        daysContainer.appendChild(emptyDay);
    }
}
function renderEvents() {
    eventsContainer.innerHTML = '';    
    eventsData.forEach(events => {
        const eventDate = new Date(events.EventDate);

        if (currentDate.getMonth() === eventDate.getMonth() && currentDate.getFullYear() === eventDate.getFullYear()) {
            const eventContainer = document.createElement('div');
            eventContainer.className = 'search-event-container';
            const eventLink = document.createElement('a');
            eventLink.href = events.Link;
            const eventImage = document.createElement('img');
            eventImage.src = events.EventImage;
            eventImage.alt = events.EventName;
            eventLink.appendChild(eventImage);
            eventContainer.appendChild(eventLink);
            const eventTitle = document.createElement('h3');
            eventTitle.id = 'title';
            const titleLink = document.createElement('a');
            titleLink.href = events.link;
            titleLink.id = 'event-link';
            titleLink.innerText = events.EventName;
            eventTitle.appendChild(titleLink);
            eventContainer.appendChild(eventTitle);
            const eventDescription = document.createElement('p');
            eventDescription.id = 'description';
            eventDescription.innerHTML = `<strong>Date:</strong> . ${events.EventWhen}<br><strong>Address:</strong> ${events.EventAddress}`;
            eventContainer.appendChild(eventDescription);
            eventsContainer.appendChild(eventContainer);
        }
        
    });       
}

function goToPreviousMonth() {
    currentDate.setMonth(currentDate.getMonth() - 1);
    renderCalendar();
    renderEvents();
}

function goToNextMonth() {
    currentDate.setMonth(currentDate.getMonth() + 1);
    renderCalendar();
    renderEvents();
}

prevMonthButton.addEventListener('click', goToPreviousMonth);
nextMonthButton.addEventListener('click', goToNextMonth);

renderCalendar();
renderEvents();
