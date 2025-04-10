import { jsPDF } from "jspdf";

export const generarPDF = (tipoDocumento, data) => {
  console.log("Objeto data:", data); // Para diagnóstico
  const doc = new jsPDF();
  doc.setFont("helvetica", "normal");

  // Encabezado de la empresa
  doc.setFontSize(18);
  doc.setFont("helvetica", "bold");
  doc.text("CLIMAS DEL DESIERTO", 105, 18, { align: "center" });

  doc.setFontSize(10);
  doc.setFont("helvetica", "normal");
  doc.text("JESUS ALBERTO LOPEZ NORIEGA", 105, 24, { align: "center" });

  doc.setFontSize(8);
  doc.text("Dirección: Av. Paseo de la Reina, 2345", 105, 28, { align: "center" });
  doc.text("Teléfono: (55) 5555-5555", 105, 32, { align: "center" });
  doc.text("Email: jesus@climasdeldesierto.com", 105, 36, { align: "center" });
  doc.text("Página Web: www.climasdeldesierto.com", 105, 40, { align: "center" });

  // Título del documento
  doc.setFontSize(12);
  doc.setFont("helvetica", "bold");
  doc.text(tipoDocumento, 14, 50);

  // Información del documento
  doc.setFontSize(8);
  doc.setFont("helvetica", "bold");
  doc.text("Número:", 14, 60);
  doc.setFont("helvetica", "normal");
  doc.text(`${data.id}`, 40, 60);
  doc.setFont("helvetica", "bold");
  doc.text("Fecha:", 140, 60);
  doc.setFont("helvetica", "normal");
  const fecha = data.fecha && !Number.isNaN(new Date(data.fecha).getTime())
    ? new Date(data.fecha).toLocaleDateString()
    : "Fecha no disponible";
  doc.text(fecha, 160, 60);
  doc.line(14, 65, 190, 65); // Línea divisoria

  // Datos del cliente en dos columnas
  const clienteInfo = [
    ["Cliente", data.cliente.nombre_razon_social],
    ["Dirección", `${data.cliente.calle} ${data.cliente.numero_exterior}${data.cliente.numero_interior ? `, Int. ${data.cliente.numero_interior}` : ''} `],
    ["RFC", data.cliente.rfc],
    ["Colonia", data.cliente.colonia],
    ["Rég. Fiscal", data.cliente.regimen_fiscal],
    ["Uso CFDI", data.cliente.uso_cfdi],
    ["Email", data.cliente.email],
    ["Teléfono", data.cliente.telefono || 'N/A'],
    ["CP", data.cliente.codigo_postal],
    ["Municipio", data.cliente.municipio],
    ["Estado", data.cliente.estado],
    ["País", data.cliente.pais],
  ];

  let yPos = 70;
  const columnWidth = 18;
  const xPosLeft = 14;
  const xPosRight = 100;

  clienteInfo.forEach((row, index) => {
    const xPos = index % 2 === 0 ? xPosLeft : xPosRight;
    if (index % 2 === 0) {
      doc.setFont("helvetica", "bold");
      doc.text(`${row[0]}:`, xPos, yPos);
      doc.setFont("helvetica", "normal");
      doc.text(row[1], xPos + columnWidth, yPos);
    } else {
      doc.setFont("helvetica", "bold");
      doc.text(`${row[0]}:`, xPos, yPos);
      doc.setFont("helvetica", "normal");
      doc.text(row[1], xPos + columnWidth, yPos);
      yPos += 6;
    }
  });

  doc.line(14, yPos + 2, 190, yPos + 2);
  yPos += 10;

  // Tabla de productos
  doc.setFont("helvetica", "bold");
  doc.text("Cantidad", 14, yPos);
  doc.text("Concepto", 40, yPos);
  doc.text("Precio Unitario", 120, yPos);
  doc.text("Subtotal", 160, yPos);
  doc.line(14, yPos + 2, 190, yPos + 2);

  yPos += 6;
  doc.setFont("helvetica", "normal");
  for (const producto of data.productos) {
    const cantidad = producto.pivot.cantidad || 1;
    const precio = producto.pivot.precio || 0;
    const subtotal = (cantidad * precio).toFixed(2);
    doc.text(`${cantidad}`, 14, yPos);
    doc.text(`${producto.nombre}`, 40, yPos);
    doc.text(`$${precio.toFixed(2)}`, 120, yPos);
    doc.text(`$${subtotal}`, 160, yPos);
    yPos += 6;
  }

  doc.line(14, yPos + 2, 190, yPos + 2);
  yPos += 10;

  // Total
  doc.setFont("helvetica", "bold");
  doc.text(`Total: $${data.total.toFixed(2)}`, 160, yPos);

  // Guardar o abrir el PDF
  doc.save(`${tipoDocumento}_${data.id}.pdf`);
};
