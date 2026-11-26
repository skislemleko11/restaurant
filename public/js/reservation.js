(() => {
    const dateInput = document.getElementById('reservation-date');
    const peopleInput = document.getElementById('reservation-people');
    const slotsContainer = document.getElementById('reservation-slots');
    const tablesContainer = document.getElementById('reservation-tables');
    const status = document.getElementById('reservation-status');
    const submitButton = document.getElementById('reservation-submit');
    const form = document.getElementById('reservation-form');
    const selectedDate = document.getElementById('selected-date');
    const selectedTime = document.getElementById('selected-time');
    const selectedPeople = document.getElementById('selected-people');
    const selectedTable = document.getElementById('selected-table');

    let activeRequest = 0;

    function clearSelection() {
        selectedTime.value = '';
        selectedTable.value = '';
        tablesContainer.replaceChildren();
        submitButton.disabled = true;
    }

    function requestUrl(path, parameters) {
        const url = new URL(path, window.location.origin);
        Object.entries(parameters).forEach(([key, value]) => url.searchParams.set(key, value));
        return url;
    }

    async function fetchJson(url) {
        const response = await fetch(url, { headers: { Accept: 'application/json' } });
        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.error || 'Nie udało się pobrać dostępności.');
        }

        return data;
    }

    async function loadTables(time, requestId) {
        clearSelection();
        selectedTime.value = time;
        status.textContent = 'Sprawdzanie stolików…';

        try {
            const data = await fetchJson(requestUrl('/reservation/tables', {
                date: dateInput.value,
                time,
                people: peopleInput.value
            }));
            if (requestId !== activeRequest) {
                return;
            }

            data.tables.forEach((table) => {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = table.is_available ? 'btn btn-outline-success' : 'btn btn-outline-secondary';
                button.disabled = !table.is_available;

                const availability = table.is_available
                    ? 'Wolny'
                    : (table.is_reserved ? 'Zajęty' : 'Za mały');
                button.textContent = `Stolik ${table.table_id} · ${table.location} · ${table.capacity} os. · ${availability}`;

                if (table.is_available) {
                    button.addEventListener('click', () => {
                        tablesContainer.querySelectorAll('button').forEach((item) => {
                            item.classList.remove('active');
                            item.setAttribute('aria-pressed', 'false');
                        });
                        button.classList.add('active');
                        button.setAttribute('aria-pressed', 'true');
                        selectedTable.value = String(table.table_id);
                        submitButton.disabled = false;
                    });
                }

                tablesContainer.append(button);
            });
            status.textContent = data.tables.some((table) => table.is_available)
                ? 'Wybierz jeden z dostępnych stolików.'
                : 'Brak wolnych stolików dla wybranych kryteriów.';
        } catch (error) {
            if (requestId === activeRequest) {
                status.textContent = error.message;
            }
        }
    }

    async function loadSlots() {
        const requestId = ++activeRequest;
        slotsContainer.replaceChildren();
        clearSelection();
        status.textContent = '';

        const people = Number(peopleInput.value);
        if (!dateInput.value || !Number.isInteger(people) || people < 1 || people > Number(peopleInput.max)) {
            status.textContent = 'Wybierz datę i poprawną liczbę osób.';
            return;
        }

        status.textContent = 'Pobieranie dostępnych godzin…';

        try {
            const data = await fetchJson(requestUrl('/reservation/slots', {
                date: dateInput.value,
                people: peopleInput.value
            }));
            if (requestId !== activeRequest) {
                return;
            }

            if (data.slots.length === 0) {
                status.textContent = 'Brak dostępnych godzin dla wybranych kryteriów.';
                return;
            }

            data.slots.forEach((slot) => {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'btn btn-outline-primary';
                button.textContent = `${slot.time} · wolne stoliki: ${slot.available_tables}`;
                button.addEventListener('click', () => {
                    slotsContainer.querySelectorAll('button').forEach((item) => {
                        item.classList.remove('btn-primary');
                        item.classList.add('btn-outline-primary');
                    });
                    button.classList.remove('btn-outline-primary');
                    button.classList.add('btn-primary');
                    selectedDate.value = dateInput.value;
                    selectedPeople.value = peopleInput.value;
                    loadTables(slot.time, ++activeRequest);
                });
                slotsContainer.append(button);
            });
            status.textContent = 'Wybierz godzinę, aby zobaczyć stoliki.';
        } catch (error) {
            if (requestId === activeRequest) {
                status.textContent = error.message;
            }
        }
    }

    dateInput.addEventListener('change', loadSlots);
    peopleInput.addEventListener('change', loadSlots);
    form.addEventListener('submit', (event) => {
        if (!selectedDate.value || !selectedTime.value || !selectedPeople.value || !selectedTable.value) {
            event.preventDefault();
            status.textContent = 'Wybierz godzinę i dostępny stolik.';
        }
    });

    void loadSlots();
})();
