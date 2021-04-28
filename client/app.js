const conn = new WebSocket('ws://192.168.178.21:9080');

conn.onopen = (event) => {
  console.log('Connection established!');
};

conn.onmessage = (msg) => {
  const msgData = JSON.parse(msg.data);
  const cmd = msgData.cmd;

  if (cmd === 'createdRoom') {
    toggleGameScreen();
  } else if (cmd === 'joinedRoom') {
    toggleGameScreen();
  } else if (cmd === 'leftRoom') {
    toggleGameScreen();
  }
};

function toggleGameScreen() {
  const gameDiv = document.querySelector('.game');
  const startScreen = document.querySelector('.start-screen');
  const overlay = document.querySelector('.overlay');
  const settings = document.querySelector('.settings-modal');
  settings.classList.remove('open');
  overlay.classList.remove('open');
  gameDiv.classList.toggle('active');
  startScreen.classList.toggle('active');
}

const createRoomButton = document.getElementById('create-room-btn');
createRoomButton.addEventListener('click', () => {
  const settings = getSettings();
  const msg = { cmd: 'createRoom', settings };
  console.dir(msg);
  conn.send(JSON.stringify(msg));
});

function getSettings() {
  const points = document.querySelector('.point-item.selected').textContent;
  const inMode = document.querySelector('.in-mode-item.selected').textContent;
  const outMode = document.querySelector('.out-mode-item.selected').textContent;

  const settings = { points, inMode, outMode };
  return settings;
}

const joinRoomButton = document.getElementById('join-room-btn');
joinRoomButton.addEventListener('click', () => {
  const roomIdField = document.getElementById('room-id');
  const roomId = roomIdField.value.toUpperCase();
  roomIdField.value = '';
  const msg = { cmd: 'joinRoom', id: roomId };
  conn.send(JSON.stringify(msg));
});

const leaveRoomButton = document.getElementById('leave-room-btn');
leaveRoomButton.addEventListener('click', () => {
  const msg = { cmd: 'leaveRoom' };
  conn.send(JSON.stringify(msg));
});

function moveSelectors() {
  const selectedItems = document.querySelectorAll('.selected');
  selectedItems.forEach((selected) => {
    const multi = Array.from(selected.parentElement.children).indexOf(selected);
    const selector = selected.parentElement.parentElement.lastElementChild;
    selector.style.left = `${multi * 10}rem`;
  });
}

moveSelectors();

const selectionItems = document.querySelectorAll('.selection-item');
selectionItems.forEach((item) => {
  item.addEventListener('click', () => {
    item.classList.add('selected');
    const siblings = getAllSiblings(item);
    siblings.forEach((sibling) => {
      sibling.classList.remove('selected');
    });
    moveSelectors();
  });
});

const openSettingsBtn = document.querySelector('#open-settings-btn');
openSettingsBtn.addEventListener('click', () => {
  const settingsModal = document.querySelector('.settings-modal');
  const overlay = document.querySelector('.overlay');
  overlay.classList.add('open');
  settingsModal.classList.add('open');
});

const cancelBtn = document.querySelector('#cancel-btn');
cancelBtn.addEventListener('click', () => {
  const settingsModal = document.querySelector('.settings-modal');
  const overlay = document.querySelector('.overlay');
  overlay.classList.remove('open');
  settingsModal.classList.remove('open');
});

const overlay = document.querySelector('.overlay');
overlay.addEventListener('click', () => {
  const modal = document.querySelector('.settings-modal.open');
  overlay.classList.remove('open');
  modal.classList.remove('open');
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

function getAllSiblings(element) {
  const siblings = [];

  if (!element.parentNode) {
    return siblings;
  }

  let sibling = element.parentNode.firstChild;

  while (sibling) {
    if (sibling.nodeType === 1 && sibling !== element) {
      siblings.push(sibling);
    }
    sibling = sibling.nextSibling;
  }
  return siblings;
}
