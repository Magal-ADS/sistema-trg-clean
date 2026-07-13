if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/service-worker.js').then((registration) => {
            registration.update();
        });
    });
}

const phoneMask = (value) => {
    const digits = value.replace(/\D/g, '').slice(0, 11);

    if (digits.length <= 2) {
        return digits;
    }

    if (digits.length <= 6) {
        return `(${digits.slice(0, 2)}) ${digits.slice(2)}`;
    }

    if (digits.length <= 10) {
        return `(${digits.slice(0, 2)}) ${digits.slice(2, 6)}-${digits.slice(6)}`;
    }

    return `(${digits.slice(0, 2)}) ${digits.slice(2, 7)}-${digits.slice(7)}`;
};

const cpfMask = (value) => {
    const digits = value.replace(/\D/g, '').slice(0, 11);

    if (digits.length <= 3) {
        return digits;
    }

    if (digits.length <= 6) {
        return `${digits.slice(0, 3)}.${digits.slice(3)}`;
    }

    if (digits.length <= 9) {
        return `${digits.slice(0, 3)}.${digits.slice(3, 6)}.${digits.slice(6)}`;
    }

    return `${digits.slice(0, 3)}.${digits.slice(3, 6)}.${digits.slice(6, 9)}-${digits.slice(9)}`;
};

document.querySelectorAll('[data-phone-mask]').forEach((input) => {
    input.value = phoneMask(input.value);

    input.addEventListener('input', () => {
        input.value = phoneMask(input.value);
    });
});

document.querySelectorAll('[data-cpf-mask]').forEach((input) => {
    input.value = cpfMask(input.value);

    input.addEventListener('input', () => {
        input.value = cpfMask(input.value);
    });
});

document.querySelectorAll('[data-customer-lookup]').forEach((lookup) => {
    const form = lookup.closest('[data-customer-form]');
    const cpfInput = lookup.querySelector('[data-customer-lookup-cpf]');
    const button = lookup.querySelector('[data-customer-lookup-button]');
    const message = lookup.querySelector('[data-customer-lookup-message]');
    const url = lookup.dataset.url;

    if (!form || !cpfInput || !button || !message || !url) {
        return;
    }

    const setMessage = (text, type = 'neutral') => {
        message.textContent = text;
        message.classList.remove('hidden', 'text-emerald-700', 'text-red-700', 'text-slate-600');
        message.classList.add(type === 'success' ? 'text-emerald-700' : type === 'error' ? 'text-red-700' : 'text-slate-600');
    };

    const setInput = (field, value) => {
        const input = form.querySelector(`[data-customer-field="${field}"]`);

        if (!input || value === null || value === undefined) {
            return;
        }

        input.value = field === 'cpf' ? cpfMask(String(value)) : String(value);
        input.dispatchEvent(new Event('input', { bubbles: true }));
    };

    const setRadio = (field, value) => {
        if (value === null || value === undefined || value === '') {
            return;
        }

        const radio = Array.from(form.querySelectorAll(`[data-customer-radio="${field}"]`))
            .find((option) => option.value === String(value));

        if (radio) {
            radio.checked = true;
        }
    };

    button.addEventListener('click', async () => {
        const cpf = cpfInput.value.replace(/\D/g, '');

        if (cpf.length !== 11) {
            setMessage('Informe um CPF valido para buscar o cadastro.', 'error');
            return;
        }

        button.disabled = true;
        setMessage('Buscando cadastro...');

        try {
            const response = await fetch(`${url}?cpf=${encodeURIComponent(cpf)}`, {
                headers: { Accept: 'application/json' },
            });

            if (!response.ok) {
                setMessage('Cadastro nao encontrado. Preencha os dados para criar o pedido.', 'error');
                return;
            }

            const payload = await response.json();
            const customer = payload.customer || {};

            setInput('name', customer.name);
            setInput('cpf', customer.cpf || cpf);
            setInput('phone', customer.phone);
            setInput('address', customer.address);
            setInput('reference', customer.reference);
            setRadio('type', customer.type);
            setRadio('city_id', customer.city_id);
            setRadio('fulfillment_type', customer.fulfillment_type);
            setRadio('payment_method', customer.payment_method);

            setMessage('Cadastro encontrado e preenchido.', 'success');
        } catch (error) {
            setMessage('Nao foi possivel buscar o cadastro agora.', 'error');
        } finally {
            button.disabled = false;
        }
    });
});
