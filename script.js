let selectedFee = 0;
let selectedEvent = "";

function openModal(eventName, fee) {
    selectedEvent = eventName;
    selectedFee = fee;
    document.getElementById('regModal').style.display = "block";
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = "none";
}

document.getElementById('registrationForm').addEventListener('submit', function(e) {
    e.preventDefault();
    closeModal('regModal');
    document.getElementById('payAmount').innerText = "Total to Pay: ₹" + selectedFee;
    document.getElementById('payModal').style.display = "block";
});

function finalSuccess() {
    // This object matches what the Flask backend expects
    const registrationData = {
        name: document.getElementById('studentName').value,
        studentID: document.getElementById('studentID').value,
        email: document.getElementById('studentEmail').value,
        event: selectedEvent,
        amount: selectedFee
    };

    fetch('/register', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(registrationData)
    })
    .then(response => response.json())
    .then(data => {
        if(data.status === "success") {
            alert("Payment Sent for Verification!\n\nThank you " + registrationData.name + ". You are registered for " + selectedEvent + ".");
            closeModal('payModal');
            document.getElementById('registrationForm').reset();
        } else {
            alert("Error: " + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert("Could not connect to the server.");
    });
}

window.onclick = function(event) {
    if (event.target.className === 'modal') {
        event.target.style.display = "none";
    }
}

// Inside finalSuccess() function in script.js
fetch('http://localhost:8080/EventProject/register', { // Update with your actual server path
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(registrationData)
})