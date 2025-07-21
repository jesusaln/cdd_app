import { jsPDF } from "jspdf";

/**
 * Configuración de la empresa
 */
const EMPRESA_CONFIG = {
  nombre: "CLIMAS DEL DESIERTO",
  propietario: "JESUS ALBERTO LOPEZ NORIEGA",
  direccion: "Av. Paseo de la Reina, 2345",
  telefono: "(55) 5555-5555",
  email: "jesus@climasdeldesierto.com",
  web: "www.climasdeldesierto.com"
};

/**
 * Configuración de estilos y dimensiones
 */
const ESTILOS = {
  margen: {
    izquierdo: 14,
    derecho: 196,
    superior: 10,
    inferior: 280
  },
  fuentes: {
    titulo: { size: 18, style: "bold" },
    subtitulo: { size: 12, style: "bold" },
    encabezado: { size: 10, style: "normal" },
    texto: { size: 8, style: "normal" },
    textoSmall: { size: 7, style: "normal" }
  },
  colores: {
    primario: [41, 128, 185], // Azul
    secundario: [52, 73, 94], // Gris oscuro
    texto: [44, 62, 80],      // Negro suave
    linea: [189, 195, 199]    // Gris claro
  }
};

/**
 * Utilidades para el PDF
 */
class PDFUtils {
  static formatearFecha(fecha) {
    if (!fecha || Number.isNaN(new Date(fecha).getTime())) {
      return new Date().toLocaleDateString('es-MX');
    }
    return new Date(fecha).toLocaleDateString('es-MX');
  }

  static formatearMoneda(valor, moneda = 'MXN') {
    const numero = Number(valor) || 0;
    return new Intl.NumberFormat('es-MX', {
      style: 'currency',
      currency: moneda
    }).format(numero);
  }

  static truncarTexto(texto, maxLength = 50) {
    if (!texto) return '';
    return texto.length > maxLength ? `${texto.substring(0, maxLength)}...` : texto;
  }

  static validarDatos(data) {
    const errores = [];

    if (!data) {
      errores.push('No se proporcionaron datos');
      return errores;
    }

    if (!data.id) errores.push('ID del documento no encontrado');
    if (!data.cliente) errores.push('Datos del cliente no encontrados');
    if (!data.productos || !Array.isArray(data.productos)) {
      errores.push('Lista de productos no válida');
    }

    return errores;
  }
}

/**
 * Clase principal para generar PDF
 */
class GeneradorPDF {
  constructor() {
    this.doc = new jsPDF();
    this.posicionY = ESTILOS.margen.superior;
    this.paginaActual = 1;
  }

  // Configurar fuente y tamaño
  setFont(size, style = 'normal') {
    this.doc.setFontSize(size);
    this.doc.setFont('helvetica', style);
  }

  // Configurar color
  setColor(color) {
    this.doc.setTextColor(...color);
  }

  // Verificar si necesita nueva página
  verificarEspacio(espacioNecesario = 20) {
    if (this.posicionY + espacioNecesario > ESTILOS.margen.inferior) {
      this.nuevaPagina();
      return true;
    }
    return false;
  }

  // Crear nueva página
  nuevaPagina() {
    this.doc.addPage();
    this.paginaActual++;
    this.posicionY = ESTILOS.margen.superior;
    this.agregarNumeroPagina();
  }

  // Agregar número de página
  agregarNumeroPagina() {
    this.setFont(ESTILOS.fuentes.textoSmall.size, ESTILOS.fuentes.textoSmall.style);
    this.setColor(ESTILOS.colores.texto);
    this.doc.text(
      `Página ${this.paginaActual}`,
      ESTILOS.margen.derecho - 20,
      290,
      { align: 'right' }
    );
  }

  // Agregar línea divisoria
  agregarLinea(y = this.posicionY) {
    this.doc.setDrawColor(...ESTILOS.colores.linea);
    this.doc.line(ESTILOS.margen.izquierdo, y, ESTILOS.margen.derecho, y);
  }

  // Encabezado de la empresa
  agregarEncabezado() {
    const { nombre, propietario, direccion, telefono, email, web } = EMPRESA_CONFIG;

    // Nombre de la empresa
    this.setFont(ESTILOS.fuentes.titulo.size, ESTILOS.fuentes.titulo.style);
    this.setColor(ESTILOS.colores.primario);
    this.doc.text(nombre, 105, this.posicionY + 10, { align: 'center' });

    // Propietario
    this.setFont(ESTILOS.fuentes.encabezado.size, ESTILOS.fuentes.encabezado.style);
    this.setColor(ESTILOS.colores.secundario);
    this.doc.text(propietario, 105, this.posicionY + 18, { align: 'center' });

    // Información de contacto
    this.setFont(ESTILOS.fuentes.texto.size, ESTILOS.fuentes.texto.style);
    this.setColor(ESTILOS.colores.texto);

    const contactoInfo = [direccion, telefono, email, web];
    contactoInfo.forEach((info, index) => {
      this.doc.text(info, 105, this.posicionY + 24 + (index * 4), { align: 'center' });
    });

    this.posicionY += 45;
    this.agregarLinea();
    this.posicionY += 8;
  }

  // Título del documento
  agregarTituloDocumento(tipoDocumento, data) {
    this.setFont(ESTILOS.fuentes.subtitulo.size, ESTILOS.fuentes.subtitulo.style);
    this.setColor(ESTILOS.colores.primario);
    this.doc.text(tipoDocumento.toUpperCase(), ESTILOS.margen.izquierdo, this.posicionY);

    // Información básica del documento
    this.posicionY += 8;
    this.setFont(ESTILOS.fuentes.texto.size, ESTILOS.fuentes.texto.style);
    this.setColor(ESTILOS.colores.texto);

    // Número y fecha en la misma línea
    this.doc.text(`Número: ${data.id}`, ESTILOS.margen.izquierdo, this.posicionY);
    this.doc.text(`Fecha: ${PDFUtils.formatearFecha(data.fecha)}`, 140, this.posicionY);

    this.posicionY += 8;
    this.agregarLinea();
    this.posicionY += 8;
  }

  // Información del cliente
  agregarInformacionCliente(cliente) {
    if (!cliente) return;

    this.verificarEspacio(50);

    this.setFont(ESTILOS.fuentes.subtitulo.size, ESTILOS.fuentes.subtitulo.style);
    this.setColor(ESTILOS.colores.secundario);
    this.doc.text('INFORMACIÓN DEL CLIENTE', ESTILOS.margen.izquierdo, this.posicionY);
    this.posicionY += 8;

    const clienteInfo = [
      ['Cliente:', cliente.nombre_razon_social || 'N/A'],
      ['RFC:', cliente.rfc || 'N/A'],
      ['Dirección:', this.construirDireccion(cliente)],
      ['Email:', cliente.email || 'N/A'],
      ['Teléfono:', cliente.telefono || 'N/A'],
      ['Régimen Fiscal:', cliente.regimen_fiscal || 'N/A'],
      ['Uso CFDI:', cliente.uso_cfdi || 'N/A']
    ];

    this.setFont(ESTILOS.fuentes.texto.size, ESTILOS.fuentes.texto.style);

    clienteInfo.forEach(([etiqueta, valor], index) => {
      const xPos = index % 2 === 0 ? ESTILOS.margen.izquierdo : 110;
      const yPos = this.posicionY + Math.floor(index / 2) * 6;

      // Etiqueta en negrita
      this.doc.setFont('helvetica', 'bold');
      this.setColor(ESTILOS.colores.secundario);
      this.doc.text(etiqueta, xPos, yPos);

      // Valor en texto normal
      this.doc.setFont('helvetica', 'normal');
      this.setColor(ESTILOS.colores.texto);
      const valorTruncado = PDFUtils.truncarTexto(valor, 35);
      this.doc.text(valorTruncado, xPos + 25, yPos);
    });

    this.posicionY += Math.ceil(clienteInfo.length / 2) * 6 + 5;
    this.agregarLinea();
    this.posicionY += 8;
  }

  construirDireccion(cliente) {
    const partes = [
      cliente.calle,
      cliente.numero_exterior,
      cliente.numero_interior ? `Int. ${cliente.numero_interior}` : null,
      cliente.colonia,
      cliente.codigo_postal,
      cliente.municipio,
      cliente.estado
    ].filter(Boolean);

    return partes.join(', ') || 'N/A';
  }

  // Tabla de productos
  agregarTablaProductos(productos) {
    if (!productos || productos.length === 0) {
      this.doc.text('No hay productos registrados', ESTILOS.margen.izquierdo, this.posicionY);
      this.posicionY += 10;
      return 0;
    }

    this.verificarEspacio(40);

    // Encabezados de la tabla
    this.setFont(ESTILOS.fuentes.texto.size, 'bold');
    this.setColor(ESTILOS.colores.secundario);

    const encabezados = [
      { texto: 'Cant.', x: ESTILOS.margen.izquierdo },
      { texto: 'Concepto', x: 35 },
      { texto: 'Precio Unit.', x: 130 },
      { texto: 'Subtotal', x: 170 }
    ];

    encabezados.forEach(encabezado => {
      this.doc.text(encabezado.texto, encabezado.x, this.posicionY);
    });

    this.posicionY += 4;
    this.agregarLinea();
    this.posicionY += 6;

    // Productos
    this.setFont(ESTILOS.fuentes.texto.size, 'normal');
    this.setColor(ESTILOS.colores.texto);

    let subtotalGeneral = 0;

    productos.forEach(producto => {
      this.verificarEspacio(8);

      const cantidad = producto.pivot?.cantidad || 1;
      const precio = producto.pivot?.precio || producto.precio || 0;
      const subtotal = cantidad * precio;
      subtotalGeneral += subtotal;

      // Datos del producto
      this.doc.text(cantidad.toString(), ESTILOS.margen.izquierdo, this.posicionY);

      const nombreTruncado = PDFUtils.truncarTexto(producto.nombre || 'Producto sin nombre', 45);
      this.doc.text(nombreTruncado, 35, this.posicionY);

      this.doc.text(PDFUtils.formatearMoneda(precio), 130, this.posicionY);
      this.doc.text(PDFUtils.formatearMoneda(subtotal), 170, this.posicionY);

      this.posicionY += 6;
    });

    this.posicionY += 4;
    this.agregarLinea();
    this.posicionY += 8;

    return subtotalGeneral;
  }

  // Totales
  agregarTotales(data, subtotalCalculado) {
    const total = data.total || subtotalCalculado;
    const iva = data.iva || (total * 0.16);
    const subtotal = data.subtotal || (total - iva);

    this.verificarEspacio(25);

    this.setFont(ESTILOS.fuentes.texto.size, 'normal');
    this.setColor(ESTILOS.colores.texto);

    const totales = [
      ['Subtotal:', subtotal],
      ['IVA (16%):', iva],
      ['TOTAL:', total]
    ];

    totales.forEach(([etiqueta, valor], index) => {
      const esTotal = index === totales.length - 1;

      if (esTotal) {
        this.setFont(ESTILOS.fuentes.subtitulo.size, 'bold');
        this.setColor(ESTILOS.colores.primario);
      }

      this.doc.text(etiqueta, 140, this.posicionY);
      this.doc.text(PDFUtils.formatearMoneda(valor), 170, this.posicionY);

      this.posicionY += esTotal ? 8 : 6;
    });
  }

  // Pie de página (opcional)
  agregarPiePagina(mensaje = '') {
    if (mensaje) {
      this.setFont(ESTILOS.fuentes.textoSmall.size, 'italic');
      this.setColor(ESTILOS.colores.texto);
      this.doc.text(mensaje, 105, 285, { align: 'center' });
    }
    this.agregarNumeroPagina();
  }
}

/**
 * Función principal para generar el PDF
 */
export const generarPDF = (tipoDocumento, data, opciones = {}) => {
  console.log("Generando PDF con datos:", data);

  try {
    // Validar datos
    const errores = PDFUtils.validarDatos(data);
    if (errores.length > 0) {
      console.error('Errores en los datos:', errores);
      throw new Error(`Datos inválidos: ${errores.join(', ')}`);
    }

    // Crear instancia del generador
    const generador = new GeneradorPDF();

    // Generar contenido
    generador.agregarEncabezado();
    generador.agregarTituloDocumento(tipoDocumento, data);
    generador.agregarInformacionCliente(data.cliente);

    const subtotalCalculado = generador.agregarTablaProductos(data.productos);
    generador.agregarTotales(data, subtotalCalculado);

    // Pie de página opcional
    if (opciones.mensaje) {
      generador.agregarPiePagina(opciones.mensaje);
    } else {
      generador.agregarNumeroPagina();
    }

    // Configurar nombre del archivo
    const nombreArchivo = opciones.nombreArchivo ||
      `${tipoDocumento.replace(/\s+/g, '_')}_${data.id}_${Date.now()}.pdf`;

    // Guardar o retornar el PDF
    if (opciones.retornarBlob) {
      return generador.doc.output('blob');
    } else if (opciones.abrirEnNuevaVentana) {
      generador.doc.output('dataurlnewwindow');
    } else {
      generador.doc.save(nombreArchivo);
    }

    console.log(`PDF "${nombreArchivo}" generado exitosamente`);
    return true;

  } catch (error) {
    console.error('Error al generar PDF:', error);
    throw new Error(`Error al generar PDF: ${error.message}`);
  }
};

/**
 * Función auxiliar para generar múltiples documentos
 */
export const generarPDFMultiple = (documentos, opciones = {}) => {
  const resultados = [];

  documentos.forEach((documento, index) => {
    try {
      const resultado = generarPDF(
        documento.tipo,
        documento.data,
        {
          ...opciones,
          nombreArchivo: `${documento.tipo}_${documento.data.id}_${index + 1}.pdf`
        }
      );
      resultados.push({ exito: true, documento: documento.tipo, resultado });
    } catch (error) {
      resultados.push({ exito: false, documento: documento.tipo, error: error.message });
    }
  });

  return resultados;
};

// Ejemplo de uso:
/*
const data = {
  id: "001",
  fecha: "2024-01-15",
  cliente: {
    nombre_razon_social: "Empresa Ejemplo S.A. de C.V.",
    rfc: "EEJ123456789",
    calle: "Calle Principal",
    numero_exterior: "123",
    numero_interior: "A",
    colonia: "Centro",
    codigo_postal: "12345",
    municipio: "Ciudad",
    estado: "Estado",
    pais: "México",
    email: "contacto@empresa.com",
    telefono: "555-1234",
    regimen_fiscal: "Régimen General",
    uso_cfdi: "Gastos en general"
  },
  productos: [
    {
      nombre: "Servicio de Instalación",
      pivot: { cantidad: 1, precio: 1500.00 }
    }
  ],
  total: 1740.00
};

// Uso básico
generarPDF("Cotización", data);

// Uso con opciones
generarPDF("Factura", data, {
  nombreArchivo: "factura_personalizada.pdf",
  mensaje: "Gracias por su preferencia",
  retornarBlob: false
});
*/
