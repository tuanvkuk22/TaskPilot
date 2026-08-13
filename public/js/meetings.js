(() => {
    const roomRoot = document.getElementById('meeting-room');
    if (!roomRoot) return;

    const meta = (name) => document.querySelector(`meta[name="${name}"]`)?.content;
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    const json = (url, options = {}) => fetch(url, { ...options, headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', ...(options.headers || {}) } }).then(async response => {
        if (!response.ok) throw new Error((await response.json()).message || 'Request failed');
        return response.json();
    });
    const videoGrid = document.getElementById('video-grid');
    const chatMessages = document.getElementById('chat-messages');
    const participants = JSON.parse(meta('meeting-members') || '[]');
    let room;
    let lastMessage = 0;
    let activeRecipient = null;
    let projectTasks = [];

    const renderMessage = (message) => {
        const item = document.createElement('article');
        item.className = 'tp-chat-message';
        item.dataset.messageId = message.id;
        const isMine = Number(message.user_id) === Number(meta('current-user-id'));
        const sourceTask = message.source_task ? `<small class="text-success">Task: ${escapeHtml(message.source_task.name)}</small>` : `<button class="tp-message-action create-task">Tạo Task</button>`;
        const controls = !message.deleted_at && isMine ? `<button class="tp-message-action edit-message">Sửa</button><button class="tp-message-action revoke-message">Thu hồi</button>` : '';
        const body = message.deleted_at ? '<em class="tp-message-revoked">Tin nhắn đã được thu hồi</em>' : escapeHtml(message.body || '');
        const edited = message.edited_at && !message.deleted_at ? ' <small>(đã sửa)</small>' : '';
        const attachments = (message.attachments || []).map(file => `<a class="tp-message-file" href="${meta('meeting-file-prefix')}/${encodeURIComponent(file.path)}" target="_blank"><i class="fa fa-file-o"></i><span>${escapeHtml(file.name)}</span></a>`).join('');
        item.innerHTML = `<div class="tp-message-head"><strong>${escapeHtml(message.user.name)}</strong><time>${new Date(message.created_at).toLocaleTimeString([], {hour: '2-digit', minute: '2-digit'})}</time>${edited}</div><p>${body}</p><div class="tp-message-files">${attachments}</div><div>${sourceTask}${controls}</div>`;
        item.querySelector('.create-task')?.addEventListener('click', () => createTask(message));
        item.querySelector('.edit-message')?.addEventListener('click', () => editMessage(message));
        item.querySelector('.revoke-message')?.addEventListener('click', () => revokeMessage(message));
        chatMessages.appendChild(item);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    };
    const escapeHtml = (value) => String(value).replace(/[&<>"']/g, character => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[character]));
    const loadMessages = () => json(`${meta('meeting-messages-url')}?after=0${activeRecipient ? `&recipient_id=${activeRecipient}` : ''}`).then(data => { chatMessages.replaceChildren(); data.messages.forEach(message => { lastMessage = Math.max(lastMessage, message.id); renderMessage(message); }); }).catch(() => {});
    const editMessage = (message) => { const body = window.prompt('Chỉnh sửa tin nhắn', message.body || ''); if (body === null || !body.trim()) return; json(`${meta('meeting-message-url')}/${message.id}`, {method: 'PATCH', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({body: body.trim()})}).then(loadMessages).catch(error => window.alert(error.message)); };
    const revokeMessage = (message) => { if (!window.confirm('Thu hồi tin nhắn này?')) return; json(`${meta('meeting-message-url')}/${message.id}`, {method: 'DELETE'}).then(loadMessages).catch(error => window.alert(error.message)); };
    const createTask = (message) => {
        const name = window.prompt('Tên Task', message.body?.slice(0, 120) || 'Action item');
        if (!name) return;
        json(`${meta('meeting-message-url')}/${message.id}/task`, { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({name}) }).then(loadMessages).catch(error => window.alert(error.message));
    };
    const publishSignal = (type, value) => { if (!room?.localParticipant) return; const payload = new TextEncoder().encode(JSON.stringify({type, value, name: room.localParticipant.name || room.localParticipant.identity})); room.localParticipant.publishData(payload, {reliable: true, topic: 'meeting-ui'}).catch(() => {}); };
    const showReaction = (reaction, name) => { const bubble = document.createElement('span'); bubble.className = 'tp-floating-reaction'; bubble.textContent = `${reaction} ${name || ''}`; document.getElementById('floating-reactions').appendChild(bubble); window.setTimeout(() => bubble.remove(), 2200); };

    document.getElementById('chat-form').addEventListener('submit', (event) => {
        event.preventDefault();
        const body = document.getElementById('chat-body').value.trim();
        const form = new FormData(event.target);
        if (!body && !document.getElementById('chat-file').files.length) return;
        const mentionIds = participants.filter(member => new RegExp(`@${member.name.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}`, 'i').test(body)).map(member => member.id);
        mentionIds.forEach(id => form.append('mention_ids[]', id));
        if (activeRecipient) form.append('recipient_id', activeRecipient);
        json(meta('meeting-message-url'), {method: 'POST', body: form}).then(() => { event.target.reset(); loadMessages(); }).catch(error => window.alert(error.message));
    });
    document.getElementById('attach-file').addEventListener('click', () => document.getElementById('chat-file').click());
    document.getElementById('chat-file').addEventListener('change', event => { document.getElementById('file-preview').innerHTML = Array.from(event.target.files).map(file => `<span><i class="fa fa-file-o"></i> ${escapeHtml(file.name)}</span>`).join(''); });
    document.getElementById('emoji-toggle').addEventListener('click', () => { const picker = document.getElementById('emoji-picker'); picker.hidden = !picker.hidden; });
    document.querySelectorAll('#emoji-picker button').forEach(button => button.addEventListener('click', () => { const input = document.getElementById('chat-body'); const start = input.selectionStart; input.value = `${input.value.slice(0, start)}${button.textContent}${input.value.slice(input.selectionEnd)}`; input.focus(); input.selectionStart = input.selectionEnd = start + button.textContent.length; }));
    const taskPicker = document.getElementById('task-picker');
    const showTasks = () => { if (!projectTasks.length) json(meta('meeting-tasks-url')).then(data => { projectTasks = data.tasks; renderTasks(''); }); else renderTasks(''); taskPicker.hidden = false; };
    const renderTasks = (query) => { const results = document.getElementById('task-results'); results.innerHTML = projectTasks.filter(task => task.name.toLowerCase().includes(query.toLowerCase())).map(task => `<button type="button" data-task-id="${task.id}"><strong>${escapeHtml(task.name)}</strong><small>${escapeHtml(task.status || '')}</small></button>`).join('') || '<p>Không tìm thấy task</p>'; results.querySelectorAll('[data-task-id]').forEach(button => button.addEventListener('click', () => { const input = document.getElementById('chat-body'); input.value = `${input.value.replace(/@task\S*$/, '')}@task #${button.dataset.taskId} ${button.querySelector('strong').textContent} `; input.focus(); taskPicker.hidden = true; })); };
    document.getElementById('chat-body').addEventListener('input', event => { if (/@task\S*$/i.test(event.target.value)) showTasks(); else if (!taskPicker.hidden) taskPicker.hidden = true; });
    document.getElementById('task-search').addEventListener('input', event => renderTasks(event.target.value));
    document.getElementById('save-note').addEventListener('click', () => json(meta('meeting-note-url'), {method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({body: document.getElementById('private-note').value})}).then(() => window.alert('Đã lưu ghi chú riêng.')).catch(error => window.alert(error.message)));
    const actions = [];
    document.getElementById('add-action')?.addEventListener('click', () => { const title = document.getElementById('action-title').value.trim(); if (!title) return; actions.push({title, due_date: document.getElementById('action-due').value || null}); document.getElementById('action-list').insertAdjacentHTML('beforeend', `<div class="tp-action-item"><strong>${escapeHtml(title)}</strong><small>${document.getElementById('action-due').value || 'Chưa có deadline'}</small></div>`); document.getElementById('action-title').value = ''; });
    document.getElementById('save-minutes')?.addEventListener('click', () => json(meta('meeting-minutes-url'), {method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({content: document.getElementById('minutes-content').value, decisions: document.getElementById('minutes-decisions').value, actions})}).then(() => window.alert('Đã lưu biên bản.')).catch(error => window.alert(error.message)));
    document.getElementById('recording-form')?.addEventListener('submit', event => { event.preventDefault(); json(meta('meeting-recording-url'), {method: 'POST', body: new FormData(event.target)}).then(() => window.alert('Đã lưu recording.')).catch(error => window.alert(error.message)); });
    document.querySelectorAll('.tp-sidebar-tabs button').forEach(button => button.addEventListener('click', () => { document.querySelectorAll('.tp-sidebar-tabs button').forEach(item => item.classList.remove('active')); document.querySelectorAll('.tp-side-panel').forEach(panel => panel.classList.add('d-none')); button.classList.add('active'); document.getElementById(button.dataset.panel).classList.remove('d-none'); document.getElementById('reaction-picker').hidden = true; if (button.dataset.panel === 'chat-panel') { activeRecipient = null; document.getElementById('chat-body').placeholder = 'Viết tin nhắn, @mention...'; loadMessages(); } }));
    document.addEventListener('click', event => { if (!event.target.closest('#reaction-toggle, #reaction-picker')) document.getElementById('reaction-picker').hidden = true; if (!event.target.closest('#task-picker, #chat-body')) taskPicker.hidden = true; });

    const attachTrack = (track, participant) => { const element = track.attach(); element.dataset.identity = participant.identity; videoGrid.querySelector('.tp-video-placeholder')?.remove(); videoGrid.appendChild(element); };
    const connectLiveKit = () => json(meta('meeting-token-url')).then(async data => {
        room = new LivekitClient.Room({adaptiveStream: true, dynacast: true});
        room.on(LivekitClient.RoomEvent.TrackSubscribed, (track, publication, participant) => attachTrack(track, participant));
        room.on(LivekitClient.RoomEvent.TrackUnsubscribed, track => track.detach().forEach(element => element.remove()));
        room.on(LivekitClient.RoomEvent.ParticipantConnected, updateParticipants);
        room.on(LivekitClient.RoomEvent.ParticipantDisconnected, updateParticipants);
        room.on(LivekitClient.RoomEvent.DataReceived, (payload, participant) => { try { const signal = JSON.parse(new TextDecoder().decode(payload)); if (signal.type === 'reaction') showReaction(signal.value, participant?.name || participant?.identity); if (signal.type === 'hand') updateHand(participant?.identity, signal.value); } catch (error) {} });
        await room.connect(data.url, data.token);
        await room.localParticipant.enableCameraAndMicrophone();
        room.localParticipant.trackPublications.forEach(publication => publication.track && attachTrack(publication.track, room.localParticipant));
        document.getElementById('connection-state').textContent = 'Đã kết nối';
        updateParticipants();
    }).catch(error => { document.getElementById('connection-state').textContent = `Không thể kết nối: ${error.message}`; });
    const updateHand = (identity, raised) => { const item = document.querySelector(`[data-participant="${CSS.escape(identity || '')}"]`); if (item) item.querySelector('.participant-hand').textContent = raised ? '✋' : ''; };
    const updateParticipants = () => { const list = document.getElementById('participants-list'); if (!list) return; const connected = room ? [room.localParticipant, ...room.remoteParticipants.values()] : []; const connectedIds = new Set(connected.map(participant => String(participant.identity))); const offline = participants.filter(member => !connectedIds.has(String(member.id))).map(member => ({identity: String(member.id), name: member.name, isOffline: true})); const all = [...connected, ...offline]; document.getElementById('participant-count').textContent = all.length; list.innerHTML = all.map(participant => `<button type="button" class="tp-action-item ${participant.isOffline ? 'is-offline' : ''}" data-participant="${escapeHtml(participant.identity)}"><span class="tp-person-avatar">${escapeHtml((participant.name || '?').slice(0, 1).toUpperCase())}</span><strong>${escapeHtml(participant.name || participant.identity)}</strong><small><span class="participant-hand"></span> ${participant.isOffline ? 'Chưa vào phòng' : (participant.isSpeaking ? 'Đang nói' : 'Chat riêng')}</small></button>`).join(''); list.querySelectorAll('[data-participant]').forEach(item => item.addEventListener('click', () => { activeRecipient = Number(item.dataset.participant) || null; document.getElementById('chat-panel').classList.remove('d-none'); document.getElementById('people-panel').classList.add('d-none'); document.querySelectorAll('.tp-sidebar-tabs button').forEach(tab => tab.classList.toggle('active', tab.dataset.panel === 'chat-panel')); document.getElementById('chat-body').placeholder = `Chat riêng với ${item.querySelector('strong').textContent}`; loadMessages(); })); };
    document.getElementById('toggle-mic').addEventListener('click', event => { if (!room?.localParticipant) return; const enabled = room.localParticipant.isMicrophoneEnabled; room.localParticipant.setMicrophoneEnabled(!enabled); event.currentTarget.classList.toggle('is-off', enabled); });
    document.getElementById('toggle-camera').addEventListener('click', event => { if (!room?.localParticipant) return; const enabled = room.localParticipant.isCameraEnabled; room.localParticipant.setCameraEnabled(!enabled); event.currentTarget.classList.toggle('is-off', enabled); });
    document.getElementById('share-screen').addEventListener('click', () => room?.localParticipant?.setScreenShareEnabled(true));
    document.getElementById('raise-hand').addEventListener('click', event => { const raised = event.currentTarget.classList.toggle('is-active'); event.currentTarget.querySelector('span').textContent = raised ? 'Hạ tay' : 'Giơ tay'; publishSignal('hand', raised); });
    document.getElementById('reaction-toggle').addEventListener('click', () => { const picker = document.getElementById('reaction-picker'); picker.hidden = !picker.hidden; });
    document.querySelectorAll('#reaction-picker [data-reaction]').forEach(button => button.addEventListener('click', () => { showReaction(button.dataset.reaction, 'Bạn'); publishSignal('reaction', button.dataset.reaction); document.getElementById('reaction-picker').hidden = true; }));
    document.getElementById('leave-room').addEventListener('click', () => { room?.disconnect(); window.location.href = meta('meeting-archive-url'); });

    updateParticipants();
    loadMessages();
    connectLiveKit();
    window.setInterval(loadMessages, 3000);
})();
