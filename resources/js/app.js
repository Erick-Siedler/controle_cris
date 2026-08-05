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
    const width = Math.max(960, chart.days.length * 62 + 90);
    const height = 340;
    const padding = { top: 30, right: 30, bottom: 55, left: 60 };
    const values = chart.days
        .map((day) => day.weight)
        .filter((value) => value !== null)
        .map(Number);

    if (chart.goal !== null) {
        values.push(Number(chart.goal));
    }

    if (!chart.days.length || !values.length) {
        participantChart.innerHTML = '<p class="py-20 text-center text-sm text-slate-500">Cadastre a meta ou uma pesagem para gerar o gráfico.</p>';
    } else {
        let minimum = Math.min(...values);
        let maximum = Math.max(...values);
        const valueRange = Math.max(maximum - minimum, 1);
        const upperMargin = Math.max(valueRange * 0.08, 0.4);
        minimum = 0;
        maximum += upperMargin;

        const plotWidth = width - padding.left - padding.right;
        const plotHeight = height - padding.top - padding.bottom;
        const x = (index) => padding.left + (
            chart.days.length === 1
                ? plotWidth / 2
                : index * plotWidth / (chart.days.length - 1)
        );
        const y = (value) => padding.top
            + (maximum - Number(value)) * plotHeight / (maximum - minimum);
        const ticks = Array.from(
            { length: 6 },
            (_, index) => minimum + (maximum - minimum) * index / 5
        );
        const grid = ticks.map((tick) => `
            <line x1="${padding.left}" y1="${y(tick)}" x2="${width - padding.right}" y2="${y(tick)}" stroke="#e2e8f0" />
            <text x="${padding.left - 12}" y="${y(tick) + 4}" text-anchor="end" font-size="11" fill="#64748b">${tick.toFixed(1).replace('.', ',')}</text>
        `).join('');
        const labels = chart.days.map((day, index) => `
            <text x="${x(index)}" y="${height - 20}" text-anchor="middle" font-size="12" fill="#475569">${day.label}</text>
        `).join('');
        const weightPoints = chart.days
            .map((day, index) => day.weight === null
                ? null
                : { x: x(index), y: y(day.weight), weight: Number(day.weight), label: day.label })
            .filter(Boolean);
        const weightPath = weightPoints
            .map((point, index) => `${index === 0 ? 'M' : 'L'} ${point.x} ${point.y}`)
            .join(' ');
        const goalLine = chart.goal === null ? '' : `
            <line x1="${padding.left}" y1="${y(chart.goal)}" x2="${width - padding.right}" y2="${y(chart.goal)}" stroke="#2563eb" stroke-width="2" />
            ${chart.days.map((day, index) => `
                <circle cx="${x(index)}" cy="${y(chart.goal)}" r="3.5" fill="#2563eb">
                    <title>Meta: ${Number(chart.goal).toFixed(2).replace('.', ',')} kg</title>
                </circle>
            `).join('')}
        `;
        const progressLine = weightPoints.length < 2 ? '' : `
            <path d="${weightPath}" fill="none" stroke="#8b5cf6" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
        `;
        const points = weightPoints.map((point) => `
            <g>
                <circle cx="${point.x}" cy="${point.y}" r="7" fill="#f97316" stroke="#8b5cf6" stroke-width="3">
                    <title>${point.label}: ${point.weight.toFixed(2).replace('.', ',')} kg</title>
                </circle>
                <text x="${point.x}" y="${point.y - 13}" text-anchor="middle" font-size="11" font-weight="600" fill="#334155">${point.weight.toFixed(1).replace('.', ',')}</text>
            </g>
        `).join('');

        participantChart.innerHTML = `
            <svg viewBox="0 0 ${width} ${height}" style="min-width: ${width}px; width: 100%;" role="img" aria-label="Progresso de ${chart.name}">
                ${grid}
                ${labels}
                ${goalLine}
                ${progressLine}
                ${points}
            </svg>
        `;
    }
});
