const conn = new WebSocket('ws://192.168.178.21:9080');

conn.onopen = (event) => {
  console.log('Connection established!');
};

conn.onmessage = (msg) => {
  console.log(msg.data);
  const msgObj = JSON.parse(msg.data);
  const ul = document.getElementsByTagName('ul')[0];
  const li = document.createElement('li');
  const text = document.createTextNode(msgObj.cmd);
  li.appendChild(text);
  ul.appendChild(li);
};

const sendButton = document.getElementById('send-button');
const text = document.getElementById('msg-field');

sendButton.addEventListener('click', () => {
  const msg = { cmd: 'msg', value: text.value };
  conn.send(JSON.stringify(msg));
  text.value = '';
});

const joinRoomButton = document.getElementById('join-room-btn');
const createRoomButton = document.getElementById('create-room-btn');
const leaveRoomButton = document.getElementById('leave-room-btn');
const roomIdField = document.getElementById('room-id');

createRoomButton.addEventListener('click', () => {
  const msg = { cmd: 'createRoom' };
  conn.send(JSON.stringify(msg));
});

joinRoomButton.addEventListener('click', () => {
  roomId = roomIdField.value;
  const msg = { cmd: 'joinRoom', id: roomId };
  conn.send(JSON.stringify(msg));
});

leaveRoomButton.addEventListener('click', () => {
  const msg = { cmd: 'leaveRoom' };
  conn.send(JSON.stringify(msg));
});

const numberButtons = document.querySelectorAll('.number');
numberButtons.forEach((button) => {
  button.addEventListener('click', () => {
    const turnList = document.querySelector('.throw-list');
    if (turnList.childElementCount < 3) {
      const turnElement = document.createElement('li');
      let text = '';
      const multi = button.dataset.multiplier;
      const value = button.dataset.value;
      if (multi === 'D' || multi === 'T') {
        text = multi;
      }
      text += value;
      const textNode = document.createTextNode(text);
      turnElement.appendChild(textNode);
      turnElement.dataset.multiplier = multi;
      turnElement.dataset.value = value;
      turnList.appendChild(turnElement);
    }
  });
});

const backButton = document.querySelector('.back-btn');
backButton.addEventListener('click', () => {
  const turnList = document.querySelector('.throw-list');
  if (turnList.childElementCount > 0) {
    turnList.removeChild(turnList.lastChild);
  }
});

const multiplierButtons = document.querySelectorAll('.multiplier');
multiplierButtons.forEach((button) => {
  button.addEventListener('click', () => {
    const multiplier = button.dataset.multiplier;
    const numberButtons = document.querySelectorAll('.number:not(.special)');
    if (numberButtons[0].dataset.multiplier === multiplier) {
      numberButtons.forEach((numberButton) => {
        numberButton.textContent = numberButton.dataset.value;
        numberButton.dataset.multiplier = 'S';
      });
    } else {
      numberButtons.forEach((numberButton) => {
        numberButton.textContent = multiplier + numberButton.dataset.value;
        numberButton.dataset.multiplier = multiplier;
      });
    }
  });
});

const sendScoreButton = document.querySelector('.send-score-btn');
sendScoreButton.addEventListener('click', () => {
  const throwElements = document.querySelectorAll('.throw-list li');
  if (throwElements.length === 3) {
    const throws = [];
    throwElements.forEach((throwElement) => {
      let multiplier = 1;
      if (throwElement.dataset.multiplier === 'D') {
        multiplier = 2;
      } else if (throwElement.dataset.multiplier === 'T') {
        multiplier = 3;
      }
      const value = throwElement.dataset.value;
      throws.push({ multiplier, value });
    });

    const msg = {
      cmd: 'score',
      throws,
    };

    conn.send(JSON.stringify(msg));
  }
});
