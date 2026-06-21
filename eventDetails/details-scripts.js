function toggleDiscussion(){
    document.getElementById("discussion").style.display = "flex"
    document.getElementById("review").style.display="none"
    document.getElementById("discussion-Button").style.color="#0D99FF"
    document.getElementById("review-Button").style.color="black"

};
function toggleReview(){
    document.getElementById("discussion").style.display = "none"
    document.getElementById("review").style.display="flex"
    document.getElementById("discussion-Button").style.color="black"
    document.getElementById("review-Button").style.color="#0D99FF"
};



function submitDiscussion(event) {
    event.preventDefault();
    const input = document.getElementById('discussion-input').value;

    const formData = new FormData();
    formData.append('discussion', input);

    fetch('save_discussion.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(() => {
        loadDiscussions();
        document.getElementById('discussion-input').value = ''; 
    });
}
function loadDiscussions() {
    fetch('discussions.txt')
        .then(response => response.text())
        .then(data => {
            document.getElementById('discussion-list').innerHTML = data.replace(/\n/g, '<br>');
        });
}

function changeButton (button){
    if (button.textContent === 'Add to Cart') {
        button.textContent = 'Added to Cart';
        button.style.backgroundColor = '#58b8fd';
    } else if (button.textContent === 'Added to Cart') {
        button.textContent = 'Add to Cart';
        button.style.backgroundColor = 'var(--event-button-color)';
    } else if (button.textContent === 'Add to Calendar') {
        button.textContent = 'Added to Calendar';
        button.style.backgroundColor = '#58b8fd';
    } else if (button.textContent === 'Added to Calendar') {
        button.textContent = 'Add to Calendar';
        button.style.backgroundColor = 'var(--event-button-color)';
    }
}

function showPopup() {
    const popup = document.getElementById("popup-container");
    popup.style.display = "block";
    const overlay = document.getElementById("bg-overlay");
    overlay.style.display = "block";
}
function closePopup() {
    const popup = document.getElementById("popup-container");
    popup.style.display = "none";
    const overlay = document.getElementById("bg-overlay");
    overlay.style.display = "none";
}

let tickets = 1;
const numTickets = document.getElementById("num-tickets");
const incrementBtn = document.getElementById("increment");
const decrementBtn = document.getElementById("decrement");
const eventPrice = document.getElementById("event-price").innerHTML;
const price = parseFloat(eventPrice.replace(/[^0-9.]/g, ''));
const popupPrice = document.getElementById("popup-price");

incrementBtn.addEventListener('click', () => {
    tickets++;
    numTickets.innerHTML = tickets;
    if (price > 0.0) {
        popupPrice.innerHTML = (tickets * price).toFixed(2);
    }
});

decrementBtn.addEventListener('click', () => {
    tickets--;
    if (tickets < 1) {
        tickets = 1;
        numTickets.innerHTML = tickets;
    }
    else {
        numTickets.innerHTML = tickets;
    }
    
    if (price > 0.0) {
        popupPrice.innerHTML = (tickets * price).toFixed(2);
    }
});

const confirmBtn = document.querySelector('.submit-tickets');
confirmBtn.addEventListener('click', function() {
    const eventIDElement = document.querySelector('.event-id');
    const eventID = parseInt(eventIDElement.textContent);
    const quantityElement = document.getElementById('num-tickets');
    const quantity = parseInt(quantityElement.textContent);
    fetch('../shoppingCart/add-to-cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `eventID=${eventID}&quantity=${quantity}`
    })
    closePopup();
});