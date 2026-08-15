const toggleButton = document.querySelector('[data-toggle-new-user]');
const newUserPanel = document.querySelector('[data-new-user-panel]');
const createUserButton = document.querySelector('[data-create-user]');
const newUserName = document.querySelector('[data-new-user-name]');
const feedback = document.querySelector('[data-new-user-feedback]');
const usersList = document.querySelector('[data-users-list]');

toggleButton?.addEventListener('click', () => {
    newUserPanel?.classList.toggle('hidden');

    if (!newUserPanel?.classList.contains('hidden')) {
        newUserName?.focus();
    }
});

createUserButton?.addEventListener('click', async () => {
    const name = newUserName?.value.trim();

    if (!name || name.length < 3) {
        feedback.textContent = 'Informe um nome com pelo menos 3 caracteres.';
        feedback.className = 'mt-2 text-sm text-red-600';
        return;
    }

    createUserButton.disabled = true;
    feedback.textContent = 'Criando usuário...';
    feedback.className = 'mt-2 text-sm text-slate-500';

    try {
        const response = await fetch(createUserButton.dataset.url, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector(
                    'meta[name="csrf-token"]'
                ).content,
            },
            body: JSON.stringify({ name }),
        });

        const result = await response.json();

        if (!response.ok) {
            throw new Error(
                result.message || 'Não foi possível criar o usuário.'
            );
        }

        const label = document.createElement('label');
        label.className = 'flex cursor-pointer items-center gap-3 rounded-lg border border-purple-300 bg-purple-50 p-3';
        label.innerHTML = `
            <input
                type="checkbox"
                name="users[]"
                value="${result.user.id}"
                checked
                class="h-4 w-4 accent-purple-700"
            >
            <span class="text-sm font-medium"></span>
        `;
        label.querySelector('span').textContent = result.user.name;
        usersList.appendChild(label);

        newUserName.value = '';
        feedback.textContent = result.message;
        feedback.className = 'mt-2 text-sm text-emerald-700';
    } catch (error) {
        feedback.textContent = error.message;
        feedback.className = 'mt-2 text-sm text-red-600';
    } finally {
        createUserButton.disabled = false;
    }
});

document.querySelectorAll('[data-dialog-open]').forEach((button) => {
    button.addEventListener('click', () => {
        document.getElementById(button.dataset.dialogOpen)?.showModal();
    });
});

document.querySelectorAll('[data-dialog-close]').forEach((button) => {
    button.addEventListener('click', () => {
        button.closest('dialog')?.close();
    });
});

const groupTabs = [...document.querySelectorAll('[data-group-tab]')];
const groupTabPanels = document.querySelectorAll('[data-group-tab-panel]');
const groupTabStorageKey = `group-scope-tab:${window.location.pathname}`;

const activateGroupTab = (tabName, focus = false) => {
    groupTabs.forEach((tab) => {
        const active = tab.dataset.groupTab === tabName;
        tab.setAttribute('aria-selected', String(active));
        tab.setAttribute('tabindex', active ? '0' : '-1');
        tab.classList.toggle('border-purple-700', active);
        tab.classList.toggle('text-purple-800', active);
        tab.classList.toggle('border-transparent', !active);
        tab.classList.toggle('text-slate-500', !active);

        if (active && focus) {
            tab.focus();
        }
    });

    groupTabPanels.forEach((panel) => {
        panel.classList.toggle('hidden', panel.dataset.groupTabPanel !== tabName);
    });

    localStorage.setItem(groupTabStorageKey, tabName);
};

groupTabs.forEach((tab, index) => {
    tab.addEventListener('click', () => activateGroupTab(tab.dataset.groupTab));
    tab.addEventListener('keydown', (event) => {
        if (!['ArrowLeft', 'ArrowRight'].includes(event.key)) {
            return;
        }

        event.preventDefault();
        const direction = event.key === 'ArrowRight' ? 1 : -1;
        const nextIndex = (index + direction + groupTabs.length) % groupTabs.length;
        activateGroupTab(groupTabs[nextIndex].dataset.groupTab, true);
    });
});

if (groupTabs.length) {
    const savedGroupTab = localStorage.getItem(groupTabStorageKey);
    activateGroupTab(
        groupTabs.some((tab) => tab.dataset.groupTab === savedGroupTab)
            ? savedGroupTab
            : 'tables'
    );
}

const dailyStateStyles = {
    unset: {
        value: '',
        symbol: '',
        label: 'Não informado',
        classes: ['border-slate-300', 'bg-white', 'text-slate-400', 'hover:bg-slate-50'],
    },
    done: {
        value: '1',
        symbol: '✓',
        label: 'Fez',
        classes: ['border-emerald-600', 'bg-emerald-600', 'text-white', 'hover:bg-emerald-700'],
    },
    'not-done': {
        value: '0',
        symbol: '✕',
        label: 'Informou que não fez',
        classes: ['border-red-600', 'bg-red-50', 'text-red-700', 'hover:bg-red-100'],
    },
};
const allDailyStateClasses = Object.values(dailyStateStyles)
    .flatMap((state) => state.classes);

const renderDailyState = (button) => {
    const state = dailyStateStyles[button.dataset.state];
    const input = button.previousElementSibling;

    input.value = state.value;
    button.textContent = state.symbol;
    button.title = state.label;
    button.setAttribute('aria-label', `${button.getAttribute('aria-label').split(' · Estado:')[0]} · Estado: ${state.label}`);
    button.classList.remove(...allDailyStateClasses);
    button.classList.add(...state.classes);
};

document.querySelectorAll('[data-daily-state-toggle]').forEach((button) => {
    renderDailyState(button);
    button.addEventListener('click', () => {
        button.dataset.state = button.dataset.state === 'unset'
            ? 'done'
            : button.dataset.state === 'done' ? 'not-done' : 'unset';
        renderDailyState(button);
    });
});

const copyDailyMessageButton = document.querySelector(
    '[data-copy-daily-message]'
);

copyDailyMessageButton?.addEventListener('click', async () => {
    const message = document.querySelector('[data-daily-message]');
    const copyFeedback = document.querySelector('[data-copy-feedback]');

    try {
        await navigator.clipboard.writeText(message.value);
        copyFeedback.textContent = 'Mensagem copiada!';
    } catch {
        message.select();
        document.execCommand('copy');
        copyFeedback.textContent = 'Mensagem copiada!';
    }
});

const participantTabs = document.querySelectorAll('[data-participant-tab]');
const participantPanels = document.querySelectorAll('[data-participant-panel]');
const selectedTabInput = document.querySelector('[data-selected-tab]');

const activateParticipantTab = (tabName) => {
    participantTabs.forEach((tab) => {
        const active = tab.dataset.participantTab === tabName;
        tab.classList.toggle('border-purple-700', active);
        tab.classList.toggle('text-purple-800', active);
        tab.classList.toggle('border-transparent', !active);
        tab.classList.toggle('text-slate-500', !active);
    });

    participantPanels.forEach((panel) => {
        panel.classList.toggle(
            'hidden',
            panel.dataset.participantPanel !== tabName
        );
    });

    if (selectedTabInput) {
        selectedTabInput.value = tabName;
    }
};

participantTabs.forEach((tab) => {
    tab.addEventListener('click', () => {
        activateParticipantTab(tab.dataset.participantTab);
    });
});

if (participantTabs.length) {
    const requestedTab = new URLSearchParams(window.location.search).get('tab');
    activateParticipantTab(
        ['chart', 'all-time'].includes(requestedTab)
            ? requestedTab
            : 'daily'
    );
}

document.querySelectorAll('[data-weight-chart]').forEach((participantChart) => {
    const participantChartData = document.getElementById(
        participantChart.dataset.chartSource
    );

    if (!participantChartData) {
        return;
    }

    const chart = JSON.parse(participantChartData.textContent);
    const escapeHtml = (value) => String(value).replace(/[&<>'"]/g, (character) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;',
    })[character]);
    const formatWeight = (value) => Number(value).toFixed(1).replace('.', ',');
    const formatSigned = (value) => `${Number(value) > 0 ? '+' : ''}${formatWeight(value)}`;
    const weightValues = chart.days
        .map((day) => day.weight)
        .filter((value) => value !== null)
        .map(Number);
    const scaleValues = [...weightValues];

    if (chart.goal !== null) {
        scaleValues.push(Number(chart.goal));
    }

    if (!chart.days.length || !scaleValues.length) {
        participantChart.innerHTML = '<p class="py-20 text-center text-sm text-slate-500">Cadastre a meta ou uma pesagem para gerar o gráfico.</p>';
        return;
    }

    const width = Math.max(1040, chart.days.length * 105 + 100);
    const height = 430;
    const padding = { top: 38, right: 34, bottom: 78, left: 58 };
    const minimum = Math.floor((Math.min(...scaleValues) - 5) / 2) * 2;
    const maximum = Math.ceil((Math.max(...scaleValues) + 1) / 2) * 2;
    const plotWidth = width - padding.left - padding.right;
    const plotHeight = height - padding.top - padding.bottom;
    const x = (index) => padding.left + (chart.days.length === 1
        ? plotWidth / 2
        : index * plotWidth / (chart.days.length - 1));
    const y = (value) => padding.top
        + (maximum - Number(value)) * plotHeight / (maximum - minimum);
    const ticks = Array.from({ length: 7 }, (_, index) => (
        minimum + (maximum - minimum) * index / 6
    ));
    const grid = ticks.map((tick) => `
        <line x1="${padding.left}" y1="${y(tick)}" x2="${width - padding.right}" y2="${y(tick)}" stroke="#d7d7d7" />
        <text x="${padding.left - 12}" y="${y(tick) + 4}" text-anchor="end" font-size="12" fill="#171717">${formatWeight(tick)}</text>
    `).join('');
    const weekSeparators = chart.days.map((day, index) => index > 0 && index % 7 === 0
        ? `<line x1="${x(index) - 52}" y1="${padding.top}" x2="${x(index) - 52}" y2="${height - padding.bottom}" stroke="#a21caf" stroke-opacity="0.24" stroke-dasharray="4 5" />`
        : '').join('');
    const labels = chart.days.map((day, index) => `
        <text transform="translate(${x(index) + 3} ${height - 48}) rotate(-48)" text-anchor="end" font-size="12" fill="#171717">${escapeHtml(day.label)}</text>
    `).join('');
    const weightPoints = chart.days.map((day, index) => day.weight === null ? null : {
        x: x(index), y: y(day.weight), weight: Number(day.weight), label: day.label,
    }).filter(Boolean);
    const weightPath = weightPoints
        .map((point, index) => `${index === 0 ? 'M' : 'L'} ${point.x} ${point.y}`)
        .join(' ');
    const goalLine = chart.goal === null ? '' : `
        <line x1="${padding.left}" y1="${y(chart.goal)}" x2="${width - padding.right}" y2="${y(chart.goal)}" stroke="#4f46e5" stroke-width="2" />
        ${chart.days.map((day, index) => `<circle cx="${x(index)}" cy="${y(chart.goal)}" r="3.5" fill="#4f6fc7"><title>Meta: ${formatWeight(chart.goal)} kg</title></circle>`).join('')}
    `;
    const progressLine = weightPoints.length < 2 ? '' : `
        <path d="${weightPath}" fill="none" stroke="#8b4de8" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
    `;
    const points = weightPoints.map((point) => `
        <g>
            <circle cx="${point.x}" cy="${point.y}" r="10" fill="#ff6500"><title>${escapeHtml(point.label)}: ${formatWeight(point.weight)} kg</title></circle>
            <circle cx="${point.x}" cy="${point.y}" r="3" fill="#8b4de8" />
            <text x="${point.x}" y="${point.y - 15}" text-anchor="middle" font-size="12" fill="#171717">${formatWeight(point.weight)}</text>
        </g>
    `).join('');
    const latestWeight = weightValues.at(-1);
    const baselineWeight = chart.initial === null ? weightValues[0] : Number(chart.initial);
    const totalChange = latestWeight === undefined ? null : latestWeight - baselineWeight;
    const remaining = latestWeight === undefined || chart.goal === null
        ? null
        : Number(chart.goal) - latestWeight;

    participantChart.innerHTML = `
        <div class="border border-fuchsia-700 bg-white" style="min-width: ${width}px; width: 100%;">
            <div class="grid h-28 grid-cols-[90px_minmax(300px,1fr)_auto] items-center gap-5 px-3">
                <img src="${escapeHtml(chart.logo)}" alt="" class="h-20 w-20 object-contain">
                <div class="flex min-w-0 items-center gap-10">
                    <strong class="bg-fuchsia-800 px-2 py-1 text-lg text-white">${escapeHtml(chart.program)}</strong>
                    <h2 class="truncate text-xl font-bold text-black">${escapeHtml(chart.name)}</h2>
                </div>
                <div class="grid grid-cols-[auto_auto_auto_auto] items-center gap-x-3 gap-y-1 pr-8 text-base text-black">
                    <span class="text-right">TOTAL ELIMINADO:</span>
                    <strong class="rounded bg-fuchsia-800 px-3 py-1 text-center text-lg text-white">${totalChange === null ? '—' : formatSigned(totalChange)}</strong>
                    <span>EM</span>
                    <strong class="text-xl">${weightValues.length} <span class="ml-2 text-base font-normal">DIAS</span></strong>
                    <span class="text-right">FALTAM:</span>
                    <strong class="rounded border border-black bg-orange-600 px-3 py-1 text-center text-lg text-white">${remaining === null ? '—' : formatSigned(remaining)}</strong>
                    <strong class="col-span-2 max-w-56 text-sm leading-tight">Para alcançar a meta do seu emagrecimento!</strong>
                </div>
            </div>
            <svg viewBox="0 0 ${width} ${height}" class="block w-full" role="img" aria-label="Progresso de ${escapeHtml(chart.name)}">
                ${grid}${weekSeparators}${labels}${goalLine}${progressLine}${points}
            </svg>
        </div>
    `;
});
