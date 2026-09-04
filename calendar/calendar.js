const eventsData =[{"EventID":2,"EventName":"Weekend Art Market","EventDate":"2026-09-05","EventAddress":"Southbank, Melbourne","EventWhen":"Sun 10:00 AM","EventImage":"images\/event2.jpg","Link":"https:\/\/example.com\/artmarket"},{"EventID":3,"EventName":"Tech Meetup","EventDate":"2026-09-06","EventAddress":"Melbourne Central","EventWhen":"Mon 6:30 PM","EventImage":"images\/event3.jpg","Link":"https:\/\/example.com\/meetup"},{"EventID":24,"EventName":"Rock Con 2026 - Board Gaming Convention","EventDate":"2026-09-18","EventAddress":"Palisades Center, West Nyack, NY","EventWhen":"Sep 18 at 5:00 PM","EventImage":"https:\/\/serpapi.com\/searches\/6a9a8eb89f07007a5386263b\/images\/NVLCFWOeLtm1PgXXEkMFxFAH7IFl-TCHzWe5CUcZlqU.png","Link":"..\/pages\/RockCon2026BoardGamingConventionSep18at500PM.php"},{"EventID":25,"EventName":"Open Hive Night!","EventDate":"2026-09-04","EventAddress":"Barcade - FIDI, Financial District","EventWhen":"Sep 4 at 7:00 PM","EventImage":"https:\/\/serpapi.com\/searches\/6a9a8eb89f07007a5386263b\/images\/lvAy_OiMXX_aLTu1FXYipVkv8KjjQY2NfuVFZlKJBFI.jpeg","Link":"..\/pages\/OpenHiveNightSep4at700PM.php"},{"EventID":31,"EventName":"Footlight Presents Makers Market & Game Night: Worlds Greatest Gamer","EventDate":"2026-09-27","EventAddress":"Footlight Presents, Ridgewood","EventWhen":"Sep 27 at 2:00 PM","EventImage":"https:\/\/encrypted-tbn1.gstatic.com\/images?q=tbn:ANd9GcQNGZP-DF8314k0yNyyevwt3nDcRe7rXxP87SkpagcpMEst_GJe5RnrrL5j3cwZJWT6aAZUPaGVIEcO-g8","Link":"..\/pages\/FootlightPresentsMakersMarketGameNightWorldsGreatestGamerSep27at200PM.php"}];
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

// Parse a "YYYY-MM-DD" string as a LOCAL date.
// Using new Date("YYYY-MM-DD") treats it as UTC midnight, which shifts the
// calendar day by one for viewers in timezones west of UTC.
function parseLocalDate(dateStr) {
    const parts = String(dateStr).split('T')[0].split(' ')[0].split('-');
    const year = parseInt(parts[0], 10);
    const month = parseInt(parts[1], 10) - 1;
    const day = parseInt(parts[2], 10);
    return new Date(year, month, day);
}

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
            const eventDate = parseLocalDate(events.EventDate);

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
        const eventDate = parseLocalDate(events.EventDate);

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
            titleLink.href = events.Link;
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
