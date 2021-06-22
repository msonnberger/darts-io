// (c) Martin Sonnberger 2021
// Dieses Projekt ist im Rahmen des
// MultiMediaTechnology Bachelorstudiums
// an der FH Salzburg entstanden.
// Kontakt: msonnberger.mmt-b2020@fh-salzburg.ac.at

//const URL = 'ws:localhost:9080';
const URL = 'wss://users.multimediatechnology.at/~fhs45907/mmp1/ws/';
const conn = new WebSocket(URL);
let roomId;
let settings = {};

conn.onopen = (event) => {
  console.log('WebSocket connection established!');
};

conn.onmessage = (msg) => {
  const msgData = JSON.parse(msg.data);
  const cmd = msgData.cmd;

  switch (cmd) {
    case 'createdRoom':
      fetchGameScreen('left');
      roomId = msgData.content.id;
      createModal(
        'Raum wurde erstellt',
        `Teile diese Raum-ID mit deinem Gegner: ${roomId}`
      );
      break;
    case 'joinedRoom':
      fetchGameScreen('right', msgData.content.scoreboards);
      settings = msgData.content.settings;
      updateSettingsModal();
      roomId = msgData.content.roomId;
      break;
    case 'otherPlayerJoined':
      removeSpinnerAndShowScoreboard();
      break;
    case 'leftRoom':
      removeGameScreen();
      startScreenSettingsModal();
      break;
    case 'scoreboard':
      updateScoreboard(msgData.content.scoreboard, msgData.content.isOwn);
      break;
    case 'newGame':
      closeModals();
      settings = msgData.content.settings;
      clearScoreboards();
      const side = msgData.content.ownTurn ? 'left' : 'right';
      toggleActiveScoreboard(side);
      updateSettingsModal();
      break;
    case 'endGame':
      location.reload();
    case 'error':
      createModal(
        'Es ist etwas schiefgelaufen',
        msgData.errorMessage,
        msgData.isFatal
      );
      break;
    default:
      break;
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

function fetchGameScreen(side, scoreboards) {
  fetch('./game-screen.html')
    .then((res) => res.text())
    .then((data) => {
      const parser = new DOMParser();
      const gameScreen = parser.parseFromString(data, 'text/html');
      const startScreen = document.querySelector('.start-screen');
      document
        .querySelector('main')
        .appendChild(gameScreen.getRootNode().body.firstChild);
      startScreen.classList.remove('active');
      document.querySelector('.game-header').classList.add('active');
      closeSettings();
      inGameSettingsModal();
      attachEventListeners();

      if (scoreboards) {
        updateScoreboard(scoreboards[0], true);
        updateScoreboard(scoreboards[1], false);
      } else {
        clearScoreboards();
      }

      if (side === 'left') {
        document.querySelector('.scoreboard.player2').classList.add('inactive');
        document.querySelector('.scoreboards .spinner').classList.add('active');
      }

      updateRoomId(roomId);
      toggleActiveScoreboard(side);
    })
    .catch((err) => console.error(err));
}

function newGame() {
  const msg = { cmd: 'newGame' };
  conn.send(JSON.stringify(msg));
  clearScoreboards();
}

const settingsPrimaryBtn = document.querySelector(
  '.settings-buttons .btn-primary'
);
settingsPrimaryBtn.addEventListener('click', () => {
  setSettingsFromModal();
  buttonId = settingsPrimaryBtn.id;
  const msg = {};
  msg.cmd = buttonId === 'create-room-btn' ? 'createRoom' : 'changeSettings';
  msg.settings = settings;

  conn.send(JSON.stringify(msg));
  closeSettings();
});

function removeSpinnerAndShowScoreboard() {
  document.querySelector('.scoreboards .spinner').classList.remove('active');
  document.querySelector('.scoreboard.player2').classList.remove('inactive');
}

function setSettingsFromModal() {
  settings.pointMode = document.querySelector(
    '.point-item.selected'
  ).textContent;
  settings.inMode = document.querySelector(
    '.in-mode-item.selected'
  ).textContent;
  settings.outMode = document.querySelector(
    '.out-mode-item.selected'
  ).textContent;
}

function updateSettingsModal() {
  const pointItems = document.querySelectorAll('.point-item');
  pointItems.forEach((item) => {
    if (item.textContent == settings.pointMode) {
      item.classList.add('selected');
      getAllSiblings(item).forEach((sibling) => {
        sibling.classList.remove('selected');
      });
    }
  });

  const inModeItems = document.querySelectorAll('.in-mode-item');
  inModeItems.forEach((item) => {
    if (item.textContent == settings.inMode) {
      item.classList.add('selected');
      getAllSiblings(item).forEach((sibling) => {
        sibling.classList.remove('selected');
      });
    }
  });

  const outModeItems = document.querySelectorAll('.out-mode-item');
  outModeItems.forEach((item) => {
    if (item.textContent == settings.outMode) {
      item.classList.add('selected');
      getAllSiblings(item).forEach((sibling) => {
        sibling.classList.remove('selected');
      });
    }
  });

  moveSelectors();
}

function inGameSettingsModal() {
  const button = document.querySelector('.settings-buttons .btn-primary');
  button.textContent = 'Speichern';
  button.id = 'send-settings-btn';

  const info = document.createElement('p');
  info.classList.add('settings-info');
  info.textContent = 'Einstellungen werden beim nächsten Spiel übernommen.';

  document
    .querySelector('.settings-modal')
    .insertBefore(info, button.parentElement);
}

function startScreenSettingsModal() {
  const modal = document.querySelector('.settings-modal');
  const info = document.querySelector('.settings-info');
  const button = document.querySelector('.settings-buttons .btn-primary');

  modal.removeChild(info);
  button.textContent = 'Raum erstellen';
  button.id = 'create-room-btn';
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
    selector.style.left = `min(${multi * 10}rem, ${multi * 20}vw`;
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

function closeModals() {
  const modals = document.querySelectorAll('.modal.open');
  modals.forEach((modal) => {
    modal.classList.remove('open');
  });
  document.querySelector('.overlay').classList.remove('open');
}

const overlay = document.querySelector('.overlay');
overlay.addEventListener('click', closeModals);

function attachEventListeners() {
  const numberButtons = document.querySelectorAll('.number');
  numberButtons.forEach((button) => {
    button.addEventListener('click', () => {
      const turnList = document.querySelector('.throw-list');
      if (turnList != null && turnList.childElementCount < 3) {
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
          numberButton.dataset.multiplier = '1';
        });
      } else {
        numberButtons.forEach((numberButton) => {
          let multiChar;
          if (multiplier === '2') multiChar = 'D';
          else if (multiplier === '3') multiChar = 'T';
          numberButton.textContent = multiChar + numberButton.dataset.value;
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
    if (throwList.textContent == 'Du bist dran!') {
      throwList.textContent = '';
    }

    const currentThrowCount = throwList.childElementCount;

    if (currentThrowCount < 3) {
      document.querySelector('#send-score-btn').disabled =
        currentThrowCount < 2;
      document.querySelector('#edit-throw-btn').disabled = false;
      const dash = currentThrowCount === 2 ? '' : ' – '; // no dash if last element gets added
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
    document.querySelector('#send-score-btn').disabled = true;
    const throwList = document.querySelector('.throw');
    if (throwList.childElementCount > 0) {
      throwList.removeChild(throwList.lastElementChild);

      editThrowButton.disabled = throwList.childElementCount < 1;
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
      document.querySelector('#send-score-btn').disabled = true;
      document.querySelector('#edit-throw-btn').disabled = true;
    }
  });
}

function updateScoreboard(newScoreboard, isOwnScoreboard) {
  toggleActiveScoreboard();
  const player = isOwnScoreboard ? '.player1' : '.player2';

  // update last throw
  const lastThrow = document.querySelector(`${player}.last-throw`);
  let lastThrowString = '';

  if (newScoreboard.lastThrow) {
    newScoreboard.lastThrow.forEach((throwElement) => {
      if (throwElement.multiplier == 2) {
        lastThrowString += 'D';
      } else if (throwElement.multiplier == 3) {
        lastThrowString += 'T';
      }
      lastThrowString += `${throwElement.value} – `;
    });
  }
  // update points
  const points = document.querySelector(`${player}.points`);

  if (points.textContent == newScoreboard.points) {
    lastThrow.textContent = 'No Score';
  } else {
    lastThrow.textContent = lastThrowString.slice(0, -3); // slice last dash
  }

  points.textContent = newScoreboard.points;

  if (points.textContent == 0) {
    // game ended
    if (isOwnScoreboard) {
      createEndScreenModal(true);
    } else {
      createEndScreenModal(false);
    }
  }

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

function clearScoreboards() {
  const emptyScoreboard = {
    lastThrow: [],
    points: settings.pointMode,
    average: '0.0',
    highScorings: {
      180: 0,
      160: 0,
      140: 0,
      120: 0,
      100: 0,
    },
  };

  updateScoreboard(emptyScoreboard, true);
  updateScoreboard(emptyScoreboard, false);
  document
    .querySelectorAll('.last-throw')
    .forEach((element) => (element.textContent = ''));
}

function toggleActiveScoreboard(side = 'toggle') {
  const leftScoreboard = document.querySelector('.scoreboard.player1');
  const rightScoreboard = document.querySelector('.scoreboard.player2');

  if (side === 'toggle') {
    leftScoreboard.classList.toggle('disabled');
    rightScoreboard.classList.toggle('disabled');
  } else if (side === 'left') {
    leftScoreboard.classList.remove('disabled');
    rightScoreboard.classList.add('disabled');
  } else if (side === 'right') {
    leftScoreboard.classList.add('disabled');
    rightScoreboard.classList.remove('disabled');
  }

  const throwList = document.querySelector('.throw');
  const throwSpinner = document.querySelector('.throw-container .spinner');

  if (!leftScoreboard.classList.contains('disabled')) {
    throwList.textContent = 'Du bist dran!';
    throwSpinner.classList.remove('active');
  } else {
    throwList.textContent = '';
    throwSpinner.classList.add('active');
  }
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

function createModal(headingText, messageText, isFatal) {
  const heading = document.createElement('h2');
  heading.classList.add('modal-heading', 'error-heading');
  heading.textContent = headingText;

  const errorText = document.createElement('p');
  errorText.classList.add('modal-text', 'error-text');
  errorText.textContent = messageText;

  const button = document.createElement('button');
  button.classList.add('btn-primary');

  if (isFatal) {
    button.textContent = 'Neu laden';
    button.addEventListener('click', reloadPage);
  } else {
    button.textContent = 'Schließen';
    button.addEventListener('click', closeModals);
  }

  const modal = document.createElement('div');
  modal.classList.add('modal', 'open', 'error-modal');
  modal.appendChild(heading);
  modal.appendChild(errorText);
  modal.appendChild(button);

  document.querySelector('main').appendChild(modal);
  document.querySelector('.overlay').classList.add('open');
}

function createEndScreenModal(winner) {
  const heading = document.createElement('h2');

  if (winner) {
    heading.classList.add('modal-heading', 'winner-heading');
    heading.textContent = 'Du hast gewonnen! 🎉';
  } else {
    heading.classList.add('modal-heading', 'loser-heading');
    heading.textContent = 'Du hast leider verloren :(';
  }

  const text = document.createElement('p');
  text.classList.add('modal-text');

  if (winner) {
    text.textContent = 'Gratulation zum Sieg!';
  } else {
    text.textContent = 'Vordere deinen Gegner zu einer Revanche heraus!';
  }

  const newGameButton = document.createElement('button');
  newGameButton.classList.add('btn-primary');
  newGameButton.textContent = 'Neues Spiel';
  newGameButton.addEventListener('click', newGame);

  const closeButton = document.createElement('button');
  closeButton.classList.add('btn-secondary');
  closeButton.textContent = 'Beenden';
  closeButton.addEventListener('click', endGame);

  const modal = document.createElement('div');
  modal.classList.add('modal', 'open');
  modal.appendChild(heading);
  modal.appendChild(text);
  modal.appendChild(closeButton);
  modal.appendChild(newGameButton);

  document.querySelector('main').appendChild(modal);
  document.querySelector('.overlay').classList.add('open');
}

function endGame() {
  const msg = { cmd: 'endGame' };
  conn.send(JSON.stringify(msg));
  location.reload();
}
