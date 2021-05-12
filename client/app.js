const conn = new WebSocket('ws://192.168.178.21:9080');
let roomId;

conn.onopen = (event) => {
  console.log('Connection established!');
};

conn.onmessage = (msg) => {
  const msgData = JSON.parse(msg.data);
  const cmd = msgData.cmd;

  if (cmd === 'createdRoom') {
    fetchGameScreen();
    roomId = msgData.content.id;
  } else if (cmd === 'joinedRoom') {
    fetchGameScreen();
    roomId = msgData.content.roomId;
  } else if (cmd === 'leftRoom') {
    removeGameScreen();
  } else if (cmd === 'ownScoreboard') {
    updateScoreboard(msgData.content.scoreboard, true);
  } else if (cmd === 'otherScoreboard') {
    updateScoreboard(msgData.content.scoreboard, false);
  }
};

function removeGameScreen() {
  const startScreen = document.querySelector('.start-screen');
  const overlay = document.querySelector('.overlay');
  const settings = document.querySelector('.settings-modal');
  const header = document.querySelector('header');

  header.classList.remove('active');
  settings.classList.remove('open');
  overlay.classList.remove('open');
  startScreen.classList.add('active');

  const game = document.querySelector('.game-wrapper');
  document.querySelector('main').removeChild(game);
}

function fetchGameScreen() {
  fetch('../server/src/game-screen.php')
    .then((res) => res.text())
    .then((data) => {
      const parser = new DOMParser();
      const gameScreen = parser.parseFromString(data, 'text/html');
      const startScreen = document.querySelector('.start-screen');
      document
        .querySelector('main')
        .appendChild(gameScreen.getRootNode().body.firstChild);
      startScreen.classList.remove('active');
      closeSettings();
      attachEventListeners();
      document.querySelector('.game-header').classList.add('active');
      updateRoomId(roomId);
    })
    .catch((err) => console.error(err));
}

const createRoomButton = document.getElementById('create-room-btn');
createRoomButton.addEventListener('click', () => {
  const settings = getSettings();
  const msg = { cmd: 'createRoom', settings };

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

const leaveRoomButton = document.querySelector('#leave-room-btn');
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

const openSettingsBtns = document.querySelectorAll('.open-settings-btn');
openSettingsBtns.forEach((button) => {
  button.addEventListener('click', () => {
    const settingsModal = document.querySelector('.settings-modal');
    const overlay = document.querySelector('.overlay');
    overlay.classList.add('open');
    settingsModal.classList.add('open');
  });
});

const cancelBtn = document.querySelector('#cancel-btn');
cancelBtn.addEventListener('click', closeSettings);

function closeSettings() {
  const settingsModal = document.querySelector('.settings-modal');
  const overlay = document.querySelector('.overlay');
  overlay.classList.remove('open');
  settingsModal.classList.remove('open');
}

const overlay = document.querySelector('.overlay');
overlay.addEventListener('click', () => {
  const modal = document.querySelector('.settings-modal.open');
  overlay.classList.remove('open');
  modal.classList.remove('open');
});

function attachEventListeners() {
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

  const dartsFields = document.querySelectorAll('.darts-field');
  dartsFields.forEach((field) => {
    field.addEventListener('click', handleDartsFieldClick);
  });

  function handleDartsFieldClick() {
    const value = this.dataset.value;
    const multiplier = this.dataset.multiplier;

    const throwList = document.querySelector('.throw');
    const currentThrowCount = throwList.childElementCount;

    if (currentThrowCount < 3) {
      const dash = currentThrowCount == 2 ? '' : ' – '; // no dash if last element gets added
      const span = document.createElement('span');
      span.dataset.value = value;
      span.dataset.multiplier = multiplier;

      let multiplierText = '';
      if (multiplier == 2) {
        multiplierText = 'D';
      } else if (multiplier == 3) {
        multiplierText = 'T';
      }

      span.textContent = multiplierText + value + dash;
      throwList.appendChild(span);
    }
  }

  const editThrowButton = document.querySelector('#edit-throw-btn');
  editThrowButton.addEventListener('click', () => {
    const throwList = document.querySelector('.throw');
    if (throwList.childElementCount > 0) {
      throwList.removeChild(throwList.lastElementChild);
    }
  });

  const sendScoreButton = document.getElementById('send-score-btn');
  sendScoreButton.addEventListener('click', () => {
    const throwElements = document.querySelectorAll('.throw span');
    if (throwElements.length === 3) {
      const throws = [];
      throwElements.forEach((throwElement) => {
        const multiplier = throwElement.dataset.multiplier;
        const value = throwElement.dataset.value;
        throws.push({ multiplier, value });
      });

      const msg = {
        cmd: 'throw',
        throws,
      };

      conn.send(JSON.stringify(msg));

      document.querySelector('.throw').textContent = '';
    }
  });
}

function updateScoreboard(newScoreboard, ownScoreboard) {
  const player = ownScoreboard ? '.player1' : '.player2';

  // update last throw
  const lastThrow = document.querySelector(`${player}.last-throw`);
  let lastThrowString = '';

  newScoreboard.lastThrow.forEach((throwElement) => {
    if (throwElement.multiplier == 2) {
      lastThrowString += 'D';
    } else if (throwElement.multiplier == 3) {
      lastThrowString += 'T';
    }
    lastThrowString += `${throwElement.value} – `;
  });

  lastThrow.textContent = lastThrowString.slice(0, -3); // slice last dash

  // update points
  const points = document.querySelector(`${player}.points`);
  points.textContent = newScoreboard.points;

  // update average
  const average = document.querySelector(`${player}.stat-value.avg`);
  average.textContent = newScoreboard.average;

  // update high scorings
  const amounts = newScoreboard.highScorings;

  document.querySelector(`${player}.plus-180`).textContent = amounts['180'];
  document.querySelector(`${player}.plus-160`).textContent = amounts['160'];
  document.querySelector(`${player}.plus-140`).textContent = amounts['140'];
  document.querySelector(`${player}.plus-120`).textContent = amounts['120'];
  document.querySelector(`${player}.plus-100`).textContent = amounts['100'];
}

function updateRoomId(roomId) {
  const roomIdSpan = document.querySelector('.room-id');
  roomIdSpan.textContent = roomId;
}

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
