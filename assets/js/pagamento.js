/* Calcula a diferença entre o valor cobrado e o valor pago. */
function calcularFalta() {

    let cobrado = parseFloat(document.getElementById('valor_cobrado')?.value) || 0;
    let pago = parseFloat(document.getElementById('valor_pago')?.value) || 0;

    if (cobrado < 0) cobrado = 0;
    if (pago < 0) pago = 0;

    let falta = cobrado - pago;

    const inputFalta = document.getElementById('valor_falta');
    if (inputFalta) {
        inputFalta.value = falta.toFixed(2);
    }
}

document.addEventListener("DOMContentLoaded", () => {
    const inputCobrado = document.getElementById('valor_cobrado');
    const inputPago = document.getElementById('valor_pago');

    if (inputCobrado && inputPago) {
        calcularFalta();

        inputCobrado.addEventListener('input', calcularFalta);
        inputPago.addEventListener('input', calcularFalta);
    }
});