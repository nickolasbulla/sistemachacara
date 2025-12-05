function calcularFalta() {
    let cobrado = parseFloat(document.getElementById('valor_cobrado')?.value) || 0;
    let pago = parseFloat(document.getElementById('valor_pago')?.value) || 0;

    if (cobrado < 0) cobrado = 0;
    if (pago < 0) pago = 0;

    let falta = cobrado - pago;

    if (document.getElementById('valor_falta')) {
        document.getElementById('valor_falta').value = falta.toFixed(2);
    }
}

document.addEventListener("DOMContentLoaded", () => {
    if (document.getElementById('valor_cobrado') && document.getElementById('valor_pago')) {
        calcularFalta();
    }
});