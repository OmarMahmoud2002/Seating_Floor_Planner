async function parseResponse(response, fallbackMessage) {
    if (response.ok) {
        return response.json();
    }

    const payload = await response.json().catch(() => null);
    const validationMessage = payload?.errors
        ? Object.values(payload.errors).flat()[0]
        : null;

    throw new Error(validationMessage || payload?.message || fallbackMessage);
}

export async function fetchEditorData(url) {
    const response = await fetch(url, {
        headers: {
            Accept: 'application/json',
        },
    });

    return parseResponse(response, 'تعذر تحميل بيانات المخطط.');
}

export async function saveEditorLayout(url, csrfToken, designJson) {
    const response = await fetch(url, {
        method: 'POST',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify({
            design_json: designJson,
        }),
    });

    return parseResponse(response, 'تعذر حفظ المخطط.');
}

export async function assignSeat(url, csrfToken, guestId, seatKey) {
    const response = await fetch(url, {
        method: 'POST',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify({
            guest_id: guestId,
            seat_key: seatKey,
        }),
    });

    return parseResponse(response, 'تعذر تجليس الضيف.');
}

export async function unassignSeat(url, csrfToken, seatKey) {
    const response = await fetch(url, {
        method: 'POST',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify({
            seat_key: seatKey,
        }),
    });

    return parseResponse(response, 'تعذر إزالة التجليس.');
}

export async function updateGuestType(url, csrfToken, guestTypeId) {
    const response = await fetch(url, {
        method: 'POST',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify({
            guest_type_id: guestTypeId || null,
        }),
    });

    return parseResponse(response, 'تعذر تحديث نوع الضيف.');
}

export async function createGuest(url, csrfToken, guest) {
    const response = await fetch(url, {
        method: 'POST',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify(guest),
    });

    return parseResponse(response, 'تعذر إضافة الضيف.');
}
