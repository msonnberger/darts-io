var conn = new WebSocket('ws://192.168.178.21:9080');

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
