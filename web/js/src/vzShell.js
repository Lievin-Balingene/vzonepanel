/** V-zone Panel shell: sidebar + theme toggle + web list filters */
export default function initVzShell() {
	document.addEventListener('alpine:init', () => {
		Alpine.data('vzShell', () => ({
			mobileOpen: false,
			collapsed: localStorage.getItem('vz-sidebar-collapsed') === '1',
			theme: localStorage.getItem('vz-theme') || 'light',
			init() {
				this.applyTheme();
				this.syncAppClass();
				this.$watch('mobileOpen', (open) => {
					document.querySelector('.app')?.classList.toggle('sidebar-open', open);
				});
				this.$watch('collapsed', () => this.syncAppClass());
			},
			toggleCollapsed() {
				this.collapsed = !this.collapsed;
				localStorage.setItem('vz-sidebar-collapsed', this.collapsed ? '1' : '0');
			},
			toggleTheme() {
				this.theme = this.theme === 'dark' ? 'light' : 'dark';
				localStorage.setItem('vz-theme', this.theme);
				this.applyTheme();
			},
			applyTheme() {
				document.documentElement.dataset.theme = this.theme;
				document.documentElement.classList.toggle('theme-dark', this.theme === 'dark');
			},
			syncAppClass() {
				document.querySelector('.app')?.classList.toggle('sidebar-collapsed', this.collapsed);
			},
		}));

		Alpine.data('vzDashboard', () => ({
			init() {
				this.initSparks();
			},
			async initSparks() {
				const cpuEl = this.$el.querySelector('.js-spark-cpu');
				const bwEl = this.$el.querySelector('.js-spark-bw');
				if (!cpuEl && !bwEl) return;

				try {
					const chartJsBundlePath = '/js/dist/chart.js-auto.min.js';
					const chartJsModule = await import(`${chartJsBundlePath}`);
					const Chart = chartJsModule.Chart;
					const cpu = Number(this.$el.dataset.cpu || 0);
					const disk = Number(this.$el.dataset.disk || 0);

					const spark = (el, color, seed) => {
						if (!el) return;
						const data = Array.from({ length: 12 }, (_, i) =>
							Math.max(4, Math.min(100, seed + Math.sin(i / 2) * 12 + (Math.random() * 8 - 4))),
						);
						new Chart(el, {
							type: 'line',
							data: {
								labels: data.map((_, i) => i),
								datasets: [
									{
										data,
										borderColor: color,
										backgroundColor: `${color}22`,
										fill: true,
										tension: 0.4,
										pointRadius: 0,
										borderWidth: 2,
									},
								],
							},
							options: {
								responsive: true,
								maintainAspectRatio: false,
								plugins: { legend: { display: false }, tooltip: { enabled: false } },
								scales: { x: { display: false }, y: { display: false, min: 0, max: 100 } },
								animation: { duration: 600 },
							},
						});
					};

					spark(cpuEl, '#14b8a6', cpu || 35);
					spark(bwEl, '#0284c7', disk || 28);
				} catch (err) {
					console.warn('V-zone charts unavailable', err);
				}
			},
		}));

		Alpine.data('vzWebList', createVzFilterList);
		Alpine.data('vzMailList', createVzFilterList);
		Alpine.data('vzDbList', createVzFilterList);
		Alpine.data('vzDnsList', createVzFilterList);
		Alpine.data('vzCronList', createVzFilterList);
		Alpine.data('vzBackupList', createVzFilterList);
		Alpine.data('vzUserList', createVzFilterList);
		Alpine.data('vzPackageList', createVzFilterList);
		Alpine.data('vzLogList', createVzFilterList);
		Alpine.data('vzFirewallList', createVzFilterList);
		Alpine.data('vzMailAccList', createVzFilterList);
		Alpine.data('vzDnsRecList', createVzFilterList);
		Alpine.data('vzIpList', createVzFilterList);

		Alpine.data('vzToolsHome', () => ({
			q: '',
			toolVisible(el) {
				const needle = this.q.trim().toLowerCase();
				if (!needle) return true;
				return (el.dataset.keys || '').includes(needle);
			},
			sectionVisible(el) {
				const needle = this.q.trim().toLowerCase();
				if (!needle) return true;
				return Array.from(el.querySelectorAll('.vz-tool')).some((tool) =>
					(tool.dataset.keys || '').includes(needle),
				);
			},
		}));
	});

	queueMicrotask(() => {
		const ok = document.querySelector('.inline-alert-success p');
		const err = document.querySelector('.inline-alert-danger p');
		if (ok) showVzToast(ok.textContent.trim(), 'success');
		if (err) showVzToast(err.textContent.trim(), 'danger');
	});
}

function createVzFilterList() {
	return {
		filter: 'all',
		query: '',
		init() {
			const input =
				this.$el.querySelector('.js-vz-live-filter') ||
				document.querySelector('.js-vz-live-filter');
			if (input) {
				this.query = (input.value || '').toLowerCase();
				input.addEventListener('input', () => {
					this.query = input.value.toLowerCase().trim();
				});
			}
		},
		isVisible(el) {
			const status = el.dataset.status || '';
			const ssl = el.dataset.ssl || '';
			const dkim = el.dataset.dkim || '';
			const type = (el.dataset.type || '').toLowerCase();
			const name = el.dataset.name || '';
			if (this.filter === 'active' && status !== 'active') return false;
			if (this.filter === 'suspended' && status !== 'suspended') return false;
			if (this.filter === 'ssl' && ssl !== 'yes') return false;
			if (this.filter === 'dkim' && dkim !== 'yes') return false;
			if (this.filter === 'mysql' && type !== 'mysql') return false;
			if (this.filter === 'pgsql' && type !== 'pgsql') return false;
			if (
				['a', 'aaaa', 'cname', 'mx', 'txt', 'ns', 'srv'].includes(this.filter) &&
				type !== this.filter
			) {
				return false;
			}
			if (this.query) {
				const hay = `${name} ${type}`;
				if (!hay.includes(this.query)) return false;
			}
			return true;
		},
		get visibleCountLabel() {
			const rows = this.$el.querySelectorAll('.vz-filter-row');
			let n = 0;
			rows.forEach((row) => {
				if (this.isVisible(row)) n += 1;
			});
			return `${n} / ${rows.length}`;
		},
	};
}

/** Lightweight toast helper */
export function showVzToast(message, type = 'info') {
	let stack = document.querySelector('.vz-toast-stack');
	if (!stack) {
		stack = document.createElement('div');
		stack.className = 'vz-toast-stack';
		document.body.appendChild(stack);
	}
	const toast = document.createElement('div');
	toast.className = `vz-toast is-${type}`;
	toast.innerHTML = `<div>${message}</div>`;
	stack.appendChild(toast);
	setTimeout(() => {
		toast.style.opacity = '0';
		toast.style.transition = 'opacity 200ms';
		setTimeout(() => toast.remove(), 220);
	}, 3200);
}
