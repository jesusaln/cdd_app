import{c as f,Q as ge,p as pe,r as ue,i as ve,o as xe,g as C,f as e,u as F,Z as be,t as a,v as W,x as ee,l as d,e as te,A as oe,s as h,F as fe}from"./vendor-d19pJyVS.js";import{A as he}from"./AppLayout-CcnrZQKI.js";import{N as ye}from"./app-BcT2PLJy.js";import{_ as we}from"./_plugin-vue_export-helper-DlAUqK2U.js";import"./utils-Dq7h7Pqt.js";const ke={class:"pagare-page min-h-screen bg-gray-50"},Ce={class:"max-w-5xl mx-auto px-6 py-8"},Fe={class:"mb-8"},De={class:"bg-gradient-to-r from-gray-900 to-gray-800 rounded-2xl p-6 text-white"},Ee={class:"flex items-center justify-between"},Me={class:"flex items-center gap-4 mt-3"},Pe={class:"text-sm text-gray-300"},$e={class:"hidden md:flex items-center gap-3"},je={class:"text-center"},_e={class:"text-2xl font-bold"},Ae={class:"text-center"},Ne={class:"text-2xl font-bold text-green-400"},ze={class:"flex flex-wrap items-center justify-between gap-4 mt-4"},Re={class:"flex items-center gap-3"},Oe={class:"flex items-center gap-2"},Te={class:"flex items-center gap-2"},Se={class:"bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden"},Be={class:"px-8 py-6 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200"},Ie={class:"flex items-center justify-between"},Ve={class:"hidden md:flex items-center gap-2"},Le={class:"text-sm text-gray-500"},He={class:"p-8"},Ue={class:"max-w-[900px] mx-auto"},Ge={class:"text-center mb-8"},qe={class:"text-3xl font-extrabold text-gray-900 mb-2"},Xe={class:"text-gray-600"},Ye={class:"flex items-center justify-center gap-2 mt-3"},Je={class:"text-sm text-gray-500"},Qe={class:"text-sm text-gray-500"},Ze={class:"grid grid-cols-1 md:grid-cols-3 gap-6 mb-8"},Ke={class:"bg-gray-50 rounded-xl p-6 border border-gray-200"},We={class:"text-lg font-bold text-gray-900"},et={class:"bg-gray-50 rounded-xl p-6 border border-gray-200"},tt={class:"text-lg font-bold text-gray-900"},ot={class:"bg-gray-50 rounded-xl p-6 border border-gray-200"},rt={class:"text-lg font-bold text-gray-900"},at={class:"grid grid-cols-2 md:grid-cols-4 gap-4 mb-8"},st={class:"bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-6 border border-green-200 text-center"},it={class:"text-2xl font-extrabold text-green-800"},nt={class:"text-xs text-green-600"},dt={class:"bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-6 border border-blue-200 text-center"},lt={class:"text-2xl font-extrabold text-blue-800"},mt={class:"bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-6 border border-purple-200 text-center"},ct={class:"text-2xl font-extrabold text-purple-800"},gt={class:"text-xs text-purple-600"},pt={class:"bg-gradient-to-br from-orange-50 to-orange-100 rounded-xl p-6 border border-orange-200 text-center"},ut={class:"text-2xl font-extrabold text-orange-800"},vt={class:"grid grid-cols-1 md:grid-cols-2 gap-6 mb-8"},xt={class:"bg-gray-50 rounded-xl p-6 border border-gray-200"},bt={class:"space-y-3"},ft={class:"text-lg font-bold text-gray-900"},ht={class:"text-sm font-medium text-gray-900"},yt={class:"text-lg font-bold text-gray-900"},wt={class:"bg-gray-50 rounded-xl p-6 border border-gray-200"},kt={class:"space-y-3"},Ct={class:"text-lg font-bold text-gray-900"},Ft={class:"text-lg font-bold text-gray-900"},Dt={class:"bg-yellow-50 border border-yellow-200 rounded-xl p-6 mb-8"},Et={class:"text-gray-800 leading-relaxed mb-4"},Mt={class:"text-gray-800 leading-relaxed"},Pt={class:"grid grid-cols-1 md:grid-cols-2 gap-8 mb-8"},$t={class:"text-center p-6 bg-gray-50 rounded-xl border-2 border-dashed border-gray-300"},jt={class:"text-lg font-semibold text-gray-900"},_t={class:"text-center p-6 bg-gray-50 rounded-xl border-2 border-dashed border-gray-300"},At={class:"text-lg font-semibold text-gray-900"},Nt={class:"text-center mt-8 pt-6 border-t border-gray-200"},zt={class:"text-xs text-gray-400 mt-2"},Rt={class:"flex justify-center mt-8"},Ot={class:"bg-white rounded-xl shadow-lg border border-gray-100 p-6"},Tt={class:"flex flex-col items-center gap-4"},St={class:"flex items-center gap-3"},Bt={class:"text-center text-gray-600"},It={class:"text-sm text-gray-500"},Vt=Object.assign({layout:he},{__name:"Pagare",props:{prestamo:{type:Object,required:!0},cliente:{type:Object,required:!0},empresa:{type:Object,required:!0},fecha_actual:{type:String,required:!0},monto_letras:{type:String,required:!0},tasa_mensual:{type:Number,required:!0},pago_mensual_letras:{type:String,required:!0}},setup(i){const r=i;f(()=>{var o,t,s,n,g,l,p;return{nombre:((o=r.empresa)==null?void 0:o.nombre)||"EMPRESA NO ESPECIFICADA",nombreCorto:((t=r.empresa)==null?void 0:t.nombre_comercial)||"EMP",rfc:((s=r.empresa)==null?void 0:s.rfc)||"RFC NO ESPECIFICADO",domicilio:((n=r.empresa)==null?void 0:n.direccion)||"Domicilio no especificado",lugarPago:((g=r.empresa)==null?void 0:g.direccion)||"Lugar de pago no especificado",telefono:((l=r.empresa)==null?void 0:l.telefono)||"Teléfono no especificado",email:((p=r.empresa)==null?void 0:p.email)||"Email no especificado"}});const x={titulo:"PAGARÉ",subtitulo:"Título Ejecutivo de Crédito",version:"3.0"},u=new ye({duration:4e3,position:{x:"right",y:"top"},types:[{type:"success",background:"#10b981",icon:!1},{type:"error",background:"#ef4444",icon:!1},{type:"warning",background:"#f59e0b",icon:!1}]}),re=ge();pe(()=>{const o=re.props.flash;o!=null&&o.success&&u.success(o.success),o!=null&&o.error&&u.error(o.error)});const m=o=>{const t=parseFloat(o),s=Number.isFinite(t)?t:0;return new Intl.NumberFormat("es-MX",{style:"currency",currency:"MXN",minimumFractionDigits:2,maximumFractionDigits:2}).format(s)},v=o=>{if(!o)return"Fecha no disponible";try{const t=new Date(o).getTime();return Number.isNaN(t)?"Fecha inválida":new Date(t).toLocaleDateString("es-MX",{day:"2-digit",month:"2-digit",year:"numeric"})}catch{return"Fecha inválida"}},D=o=>{if(!o)return"Fecha no disponible";try{const t=new Date(o).getTime();return Number.isNaN(t)?"Fecha inválida":new Date(t).toLocaleString("es-MX",{day:"2-digit",month:"2-digit",year:"numeric",hour:"2-digit",minute:"2-digit"})}catch{return"Fecha inválida"}},E=()=>{var o;return`CDD-${new Date().getFullYear()}-${String(((o=r.prestamo)==null?void 0:o.id)??"").padStart(4,"0")}`},b=f(()=>{var o;return(o=r.prestamo)!=null&&o.fecha_primer_pago?v(r.prestamo.fecha_primer_pago):"Fecha no disponible"}),M=f(()=>{var t,s;if(!((t=r.prestamo)!=null&&t.fecha_inicio)||!((s=r.prestamo)!=null&&s.numero_pagos))return"Fecha no disponible";const o=new Date(r.prestamo.fecha_inicio);return o.setMonth(o.getMonth()+Number(r.prestamo.numero_pagos||0)),v(o)});f(()=>{var o,t,s,n;return{montoTotal:m(((o=r.prestamo)==null?void 0:o.monto_prestado)||0),pagoMensual:m(((t=r.prestamo)==null?void 0:t.pago_periodico)||0),numeroPagos:((s=r.prestamo)==null?void 0:s.numero_pagos)||0,tasaInteres:`${(r.tasa_mensual||0).toFixed(2)}%`,fechaInicio:v((n=r.prestamo)==null?void 0:n.fecha_inicio),primerPago:b.value}});const P=()=>{var t,s,n,g,l,p;const o=[];(t=r.cliente)!=null&&t.nombre_razon_social||o.push("Nombre/Razón social del cliente"),(s=r.prestamo)!=null&&s.monto_prestado||o.push("Monto del préstamo"),(n=r.prestamo)!=null&&n.pago_periodico||o.push("Pago mensual"),(g=r.prestamo)!=null&&g.numero_pagos||o.push("Número de pagos"),(l=r.prestamo)!=null&&l.fecha_inicio||o.push("Fecha de inicio"),(p=r.prestamo)!=null&&p.fecha_primer_pago||o.push("Fecha del primer pago"),o.length&&u.open({type:"warning",message:`Faltan datos: ${o.join(", ")}`})},y={carta:{nombre:"Carta",dimensiones:"216mm 279mm",margin:"4mm 6mm 4mm 6mm"},oficio:{nombre:"Oficio",dimensiones:"216mm 330mm",margin:"4mm 6mm 4mm 6mm"},a4:{nombre:"A4",dimensiones:"210mm 297mm",margin:"4mm 6mm 4mm 6mm"}},c=ue("carta"),w=(o="carta")=>{try{P();const t=ae(o),s=window.open("","_blank","width=900,height=1100,scrollbars=yes,resizable=yes");if(!s){u.error("Habilita las ventanas emergentes para generar el PDF.");return}s.document.open(),s.document.write(t),s.document.close(),s.document.head.insertAdjacentHTML("beforeend",`
      <style>
        /* Ocultar elementos del navegador */
        * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }

        /* Asegurar que el contenido se vea correctamente */
        body { margin: 0; padding: 0; }

        /* Evitar problemas de about:blank */
        html { background: white !important; }
      </style>
    `),s.onload=function(){setTimeout(()=>{s.focus(),s.print()},300)},setTimeout(()=>{s.closed||(s.focus(),s.print())},1e3)}catch(t){console.error("Error generando PDF:",t),u.error("Error al generar el pagaré PDF")}},ae=(o="carta")=>{var R,O,T,S,B,I,V,L,H,U,G,q,X,Y,J,Q,Z,K;const t=`PAG-${String(((R=r.prestamo)==null?void 0:R.id)??"").padStart(6,"0")}`,s=v(r.fecha_actual),n=((O=r.empresa)==null?void 0:O.nombre)||"ACREEDOR NO ESPECIFICADO",g=(T=r.empresa)!=null&&T.nombre_comercial?` • ${(S=r.empresa)==null?void 0:S.nombre_comercial}`:"",l=((B=r.empresa)==null?void 0:B.direccion)||"Domicilio del acreedor no especificado",p=((I=r.empresa)==null?void 0:I.rfc)||"RFC no especificado",k=((V=r.cliente)==null?void 0:V.nombre_razon_social)||"DEUDOR NO ESPECIFICADO",$=((L=r.cliente)==null?void 0:L.direccion_completa)||"Domicilio del deudor no especificado",j=((H=r.cliente)==null?void 0:H.rfc)||"RFC no especificado",_=m((U=r.prestamo)==null?void 0:U.monto_prestado),se=m((G=r.prestamo)==null?void 0:G.pago_periodico),ie=((q=r.prestamo)==null?void 0:q.numero_pagos)??"N/D",A=(r.tasa_mensual??0).toFixed(2),ne=b.value,de=M.value,N=`Contrato de Préstamo No. ${((X=r.prestamo)==null?void 0:X.id)??"N/A"}`,z=(((Y=r.prestamo)==null?void 0:Y.notas)||((J=r.prestamo)==null?void 0:J.observaciones)||"").toString().trim(),le=z?`<div class="notas-title">Notas</div><div class="notas-text">${z.replace(/\n/g,"<br>")}</div>`:"",me=((Q=y[o])==null?void 0:Q.dimensiones)||"216mm 279mm",ce=((Z=y[o])==null?void 0:Z.margin)||"2mm 3mm 2mm 3mm";return`
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <title>${t} • Pagaré</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <style>
    /* ======== PÁGINA ======== */
    @page { size: ${me}; margin: ${ce}; }
    :root {
      --accent: #0B3D2E;
      --border: #0F172A;
      --muted: #334155;
      --ink: #111827;
      --bg-soft: #F8FAFC;
    }
    * { box-sizing: border-box; }
    html, body {
      background: #fff;
      color: var(--ink);
      font-family: "Times New Roman", Times, serif;
      font-size: 10px;
      line-height: 1.3;
    }
    body { margin: 0; }

    /* ======== HEADER / FOOTER FIJOS ======== */
     header {
       position: fixed;
       top: 0; left: 0; right: 0;
       height: 3mm;
       padding-top: 0.2mm;
       /* border-bottom: 1px solid var(--border); */
     }
    .h-inner {
      display: grid;
      grid-template-columns: 1fr auto;
      gap: 8mm;
      align-items: center;
    }
    .brand {
      font-size: 15px;
      font-weight: 700;
      letter-spacing: .3px;
      color: var(--accent);
      text-transform: uppercase;
    }
    .folio {
      padding: 6px 10px;
      border: 1px solid var(--border);
      border-radius: 4px;
      font-weight: 700;
    }
    .h-meta {
      margin-top: 0.2mm;
      color: var(--muted);
      font-size: 8px;
    }

    footer {
      position: fixed;
      bottom: 0; left: 0; right: 0;
      height: 5mm;
      border-top: 1px solid var(--border);
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 1mm;
      color: var(--muted);
      font-size: 4px;
    }
    .foot-left { max-width: 70%; }
    .foot-right { text-align: right; }

    /* Empuje del contenido para no solaparse con header/footer */
     .page-wrap { padding: 5mm 0 4mm; }

    /* ======== TÍTULO Y LUGAR/FECHA ======== */
     .title {
       text-align: center;
       margin: 2mm 0 1mm;
       font-weight: 800;
       font-size: 17px;
       letter-spacing: .7px;
     }
     .subtitle {
       text-align: center;
       color: var(--muted);
       margin-bottom: 0.5mm;
       font-size: 10.5px;
     }
     .place-date {
       text-align: center;
       color: var(--muted);
       margin-bottom: 4mm;
       font-size: 8.5px;
     }

    /* ======== BLOQUES ======== */
     .block {
       border: 1px solid var(--border);
       border-radius: 5px;
       padding: 5mm;
       margin-bottom: 5mm;
       background: #fff;
     }
    .block.soft {
      background: var(--bg-soft);
      border-color: #CBD5E1;
    }
    .block-title {
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .4px;
      color: var(--accent);
      margin-bottom: 2mm;
      font-size: 11px;
    }

    /* ======== GRID 2 COLS ======== */
     .grid-2 {
       display: grid;
       grid-template-columns: 1fr 1fr;
       gap: 5mm;
     }

    /* ======== TABLE RESUMEN ======== */
     table {
       width: 100%;
       border-collapse: collapse;
       font-size: 9.5px;
     }
     th, td {
       padding: 5px 7px;
       border: 1px solid #CBD5E1;
       vertical-align: top;
     }
    th {
      background: #EFF6FF;
      text-align: left;
      font-weight: 700;
      color: #0F172A;
    }
    td.label {
      width: 35%;
      color: var(--muted);
      font-weight: 600;
    }
    .kpi {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 3mm;
      margin-top: 1mm;
    }
    .pill {
      border: 1px solid var(--border);
      border-radius: 4px;
      padding: 3mm;
      background: #fff;
      text-align: center;
    }
    .pill .small { color: var(--muted); font-size: 8px; }
    .pill .big { font-size: 12px; font-weight: 800; margin-top: 0.5mm; }

    /* ======== CLÁUSULAS ======== */
    .clauses ol { margin: 0; padding-left: 4mm; }
    .clauses li { margin: 2mm 0; text-align: justify; }

    /* ======== FIRMAS ======== */
     .signs {
       display: grid;
       grid-template-columns: 1fr 1fr;
       gap: 10mm;
       margin-top: 6mm;
       page-break-inside: avoid;
     }
     .sign {
       text-align: center;
       padding: 8mm 5mm 5mm;
       border: 1px dashed #94A3B8;
       border-radius: 5px;
       background: #FFFFFF;
     }
    .line {
      height: 20px;
      border-bottom: 1px solid var(--border);
      margin: 0 auto 2mm;
      width: 75%;
    }
    .sign .name { font-weight: 700; }
    .sign .role { color: var(--muted); font-size: 9px; }

    /* ======== NOTAS (en footer) ======== */
    .notas-title {
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .4px;
      margin-bottom: 2mm;
      color: var(--accent);
    }
    .notas-text {
      font-size: 9.5px;
      color: var(--ink);
      line-height: 1.35;
    }

    /* ======== MEDIA PRINT ======== */
    @media print {
      a { color: inherit; text-decoration: none; }
      .no-print { display: none !important; }
      html, body {
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
      }
    }
  </style>
</head>
<body>
  <!-- HEADER -->
  <header>
    <div class="h-inner" style="padding:0 2mm;">
      <div>
        <div class="brand">${n}${g}</div>
        <div class="h-meta">${l}</div>
      </div>
      <div class="folio">${t}</div>
    </div>
  </header>

  <!-- FOOTER -->
  <footer>
    <div class="foot-left">
      ${le}
    </div>
    <div class="foot-right">
      <div>Generado: ${D(new Date)}</div>
      <div>${N}</div>
    </div>
  </footer>

  <!-- CONTENIDO -->
  <div class="page-wrap">
    <div class="title">PAGARÉ</div>
    <div class="subtitle">Título Ejecutivo de Crédito</div>
    <div class="place-date">Hermosillo, Sonora, México • ${s}</div>

    <!-- PROMESA DE PAGO -->
    <div class="block">
      <div class="block-title">Promesa de pago</div>
      <p style="text-align:justify;">
        Por este medio, yo <strong>${k}</strong>, con domicilio en <strong>${$}</strong> (RFC: <strong>${j}</strong>),
        me obligo incondicionalmente a pagar a la orden de <strong>${n}</strong> (RFC: <strong>${p}</strong>),
        en <strong>${l}</strong>, la cantidad de <strong>${_}</strong> (${r.monto_letras}),
        más intereses ordinarios a razón de <strong>${A}% mensual</strong>, pagaderos mensualmente junto con cada exhibición de capital.
      </p>
    </div>

    <!-- PARTES -->
    <div class="grid-2">
      <div class="block soft">
        <div class="block-title">Deudor</div>
        <table>
          <tr><td class="label">Nombre</td><td>${k}</td></tr>
          <tr><td class="label">Domicilio</td><td>${$}</td></tr>
          <tr><td class="label">RFC</td><td>${j}</td></tr>
        </table>
      </div>
      <div class="block soft">
        <div class="block-title">Acreedor</div>
        <table>
          <tr><td class="label">Beneficiario</td><td>${n}${g?` (${(K=r.empresa)==null?void 0:K.nombre_comercial})`:""}</td></tr>
          <tr><td class="label">Lugar de pago</td><td>${l}</td></tr>
          <tr><td class="label">RFC</td><td>${p}</td></tr>
        </table>
      </div>
    </div>

    <!-- RESUMEN FINANCIERO -->
    <div class="block">
      <div class="block-title">Resumen financiero</div>
      <table>
        <tr>
          <th>Monto del préstamo</th>
          <th>Pago mensual</th>
          <th>Tasa mensual</th>
          <th>Número de pagos</th>
          <th>Primer pago</th>
          <th>Vencimiento</th>
        </tr>
        <tr>
          <td>${_} <br><span style="color:var(--muted);">${r.monto_letras}</span></td>
          <td>${se} <br><span style="color:var(--muted);">${r.pago_mensual_letras}</span></td>
          <td>${A}%</td>
          <td>${ie}</td>
          <td>${ne}</td>
          <td>${de}</td>
        </tr>
      </table>

      <div class="kpi">
        <div class="pill">
          <div class="small">Referencia</div>
          <div class="big">${t}</div>
        </div>
        <div class="pill">
          <div class="small">Contrato</div>
          <div class="big">${N}</div>
        </div>
      </div>
    </div>

    <!-- CLÁUSULAS -->
    <div class="block">
      <div class="block-title">Cláusulas</div>
      <div class="clauses">
        <ol>
          <li><strong>Intereses moratorios.</strong> En caso de incumplimiento, se causarán intereses moratorios al doble de la tasa ordinaria sobre saldos vencidos hasta su total liquidación.</li>
          <li><strong>Vencimiento anticipado.</strong> La falta de dos pagos consecutivos o tres no consecutivos facultará al acreedor a dar por vencidas todas las obligaciones pendientes.</li>
          <li><strong>Gastos y costas.</strong> Todos los gastos de cobranza, honorarios y costas judiciales o extrajudiciales correrán a cargo del deudor.</li>
          <li><strong>Jurisdicción.</strong> Para la interpretación y cumplimiento del presente pagaré, las partes se someten a los tribunales competentes de Hermosillo, Sonora, renunciando al fuero que por razón de su domicilio presente o futuro pudiera corresponderles.</li>
          <li><strong>Cesión.</strong> Este título es negociable mediante endoso sin necesidad de notificar al deudor.</li>
          <li><strong>Naturaleza del título.</strong> El presente documento constituye título ejecutivo de conformidad con la Ley General de Títulos y Operaciones de Crédito. Requiere firma autógrafa del deudor.</li>
        </ol>
      </div>
    </div>

    <!-- FIRMAS -->
    <div class="signs">
      <div class="sign">
        <div class="line"></div>
        <div class="name">${k}</div>
        <div class="role">Deudor • Firma autógrafa</div>
      </div>
      <div class="sign">
        <div class="line"></div>
        <div class="name">${n}</div>
        <div class="role">Beneficiario (Acreedor) • Representante legal</div>
      </div>
    </div>
  </div>
</body>
</html>
`};return(o,t)=>{var s;return xe(),ve(fe,null,[C(F(be),{title:"Pagaré del Préstamo"}),e("div",ke,[e("div",Ce,[e("div",Fe,[e("div",De,[e("div",Ee,[e("div",null,[t[6]||(t[6]=e("h1",{class:"text-3xl font-bold tracking-tight mb-2"},"Pagaré Digital",-1)),t[7]||(t[7]=e("p",{class:"text-gray-300 text-lg"},"Documento legal de obligación financiera",-1)),e("div",Me,[t[5]||(t[5]=e("span",{class:"inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-500/20 text-green-300 border border-green-400/30"}," ✅ Documento Válido ",-1)),e("span",Pe," Referencia: "+a(E()),1)])]),e("div",$e,[e("div",je,[e("div",_e,a(i.prestamo.numero_pagos),1),t[8]||(t[8]=e("div",{class:"text-xs text-gray-300 uppercase tracking-wide"},"Pagos",-1))]),t[10]||(t[10]=e("div",{class:"w-px h-12 bg-gray-600"},null,-1)),e("div",Ae,[e("div",Ne,a(m(i.prestamo.monto_prestado)),1),t[9]||(t[9]=e("div",{class:"text-xs text-gray-300 uppercase tracking-wide"},"Monto Total",-1))])])])]),e("div",ze,[e("div",Re,[e("div",Oe,[t[12]||(t[12]=e("label",{class:"text-sm font-medium text-gray-700"},"Tamaño de papel:",-1)),W(e("select",{"onUpdate:modelValue":t[0]||(t[0]=n=>c.value=n),onChange:t[1]||(t[1]=n=>w(c.value)),class:"border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"},t[11]||(t[11]=[e("option",{value:"carta"},'Carta (8.5" × 11")',-1),e("option",{value:"oficio"},'Oficio (8.5" × 13")',-1),e("option",{value:"a4"},"A4 (210mm × 297mm)",-1)]),544),[[ee,c.value]])]),t[15]||(t[15]=e("div",{class:"w-px h-8 bg-gray-300"},null,-1)),e("button",{onClick:t[2]||(t[2]=n=>w(c.value)),class:"inline-flex items-center px-6 py-3 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"},t[13]||(t[13]=[e("svg",{class:"w-5 h-5 mr-2",fill:"none",stroke:"currentColor",viewBox:"0 0 24 24"},[e("path",{"stroke-linecap":"round","stroke-linejoin":"round","stroke-width":"2",d:"M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"})],-1),d(" Generar PDF ")])),e("button",{onClick:P,class:"inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-xl text-gray-700 bg-white hover:bg-gray-50 transition-colors"},t[14]||(t[14]=[e("svg",{class:"w-4 h-4 mr-2",fill:"none",stroke:"currentColor",viewBox:"0 0 24 24"},[e("path",{"stroke-linecap":"round","stroke-linejoin":"round","stroke-width":"2",d:"M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"})],-1),d(" Validar Datos ")]))]),e("div",Te,[C(F(oe),{href:`/prestamos/${i.prestamo.id}`,class:"inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-xl text-gray-700 bg-white hover:bg-gray-50 transition-colors"},{default:te(()=>t[16]||(t[16]=[e("svg",{class:"w-4 h-4 mr-2",fill:"none",stroke:"currentColor",viewBox:"0 0 24 24"},[e("path",{"stroke-linecap":"round","stroke-linejoin":"round","stroke-width":"2",d:"M15 12a3 3 0 11-6 0 3 3 0 016 0z"}),e("path",{"stroke-linecap":"round","stroke-linejoin":"round","stroke-width":"2",d:"M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"})],-1),d(" Ver Préstamo ")])),_:1},8,["href"]),C(F(oe),{href:"/prestamos",class:"inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-xl text-gray-700 bg-white hover:bg-gray-50 transition-colors"},{default:te(()=>t[17]||(t[17]=[e("svg",{class:"w-4 h-4 mr-2",fill:"none",stroke:"currentColor",viewBox:"0 0 24 24"},[e("path",{"stroke-linecap":"round","stroke-linejoin":"round","stroke-width":"2",d:"M10 19l-7-7m0 0l7-7m-7 7h18"})],-1),d(" Volver a Préstamos ")])),_:1})])])]),e("div",Se,[e("div",Be,[e("div",Ie,[t[19]||(t[19]=e("div",null,[e("h2",{class:"text-2xl font-bold text-gray-900 mb-2"},"Vista Previa del Documento"),e("p",{class:"text-gray-600"}," Versión preliminar del pagaré oficial • El PDF final incluye formato A4 profesional ")],-1)),e("div",Ve,[t[18]||(t[18]=e("span",{class:"inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800"},[e("svg",{class:"w-3 h-3 mr-1",fill:"currentColor",viewBox:"0 0 20 20"},[e("path",{"fill-rule":"evenodd",d:"M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z","clip-rule":"evenodd"})]),d(" Documento Legal ")],-1)),e("span",Le,"Versión "+a(x.version),1)])])]),e("div",He,[e("div",Ue,[e("div",Ge,[t[21]||(t[21]=e("div",{class:"inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-4"},[e("svg",{class:"w-8 h-8 text-green-600",fill:"none",stroke:"currentColor",viewBox:"0 0 24 24"},[e("path",{"stroke-linecap":"round","stroke-linejoin":"round","stroke-width":"2",d:"M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"})])],-1)),e("h1",qe,a(x.titulo),1),e("p",Xe,a(x.subtitulo),1),e("div",Ye,[e("span",Je,a(i.empresa.direccion),1),t[20]||(t[20]=e("span",{class:"text-gray-300"},"•",-1)),e("span",Qe,a(v(i.fecha_actual)),1)])]),e("div",Ze,[e("div",Ke,[t[22]||(t[22]=h('<div class="flex items-center mb-3" data-v-ce9aa003><div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3" data-v-ce9aa003><svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" data-v-ce9aa003><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" data-v-ce9aa003></path></svg></div><div data-v-ce9aa003><div class="text-sm font-medium text-gray-900" data-v-ce9aa003>Referencia</div><div class="text-xs text-gray-500" data-v-ce9aa003>Número de contrato</div></div></div>',1)),e("div",We,a(E()),1)]),e("div",et,[t[23]||(t[23]=h('<div class="flex items-center mb-3" data-v-ce9aa003><div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3" data-v-ce9aa003><svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" data-v-ce9aa003><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m-6 9l6-6m0 0v6m0-6h-6" data-v-ce9aa003></path></svg></div><div data-v-ce9aa003><div class="text-sm font-medium text-gray-900" data-v-ce9aa003>Fecha de Inicio</div><div class="text-xs text-gray-500" data-v-ce9aa003>Primer pago</div></div></div>',1)),e("div",tt,a(b.value),1)]),e("div",ot,[t[24]||(t[24]=h('<div class="flex items-center mb-3" data-v-ce9aa003><div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center mr-3" data-v-ce9aa003><svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" data-v-ce9aa003><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" data-v-ce9aa003></path></svg></div><div data-v-ce9aa003><div class="text-sm font-medium text-gray-900" data-v-ce9aa003>Lugar de Pago</div><div class="text-xs text-gray-500" data-v-ce9aa003>Jurisdicción</div></div></div>',1)),e("div",rt,a(i.empresa.direccion),1)])]),e("div",at,[e("div",st,[t[25]||(t[25]=e("div",{class:"text-green-600 mb-2"},[e("svg",{class:"w-8 h-8 mx-auto",fill:"none",stroke:"currentColor",viewBox:"0 0 24 24"},[e("path",{"stroke-linecap":"round","stroke-linejoin":"round","stroke-width":"2",d:"M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"})])],-1)),t[26]||(t[26]=e("div",{class:"text-xs text-green-700 uppercase tracking-wide font-semibold mb-1"},"Monto Total",-1)),e("div",it,a(m(i.prestamo.monto_prestado)),1),e("div",nt,a(i.monto_letras),1)]),e("div",dt,[t[27]||(t[27]=e("div",{class:"text-blue-600 mb-2"},[e("svg",{class:"w-8 h-8 mx-auto",fill:"none",stroke:"currentColor",viewBox:"0 0 24 24"},[e("path",{"stroke-linecap":"round","stroke-linejoin":"round","stroke-width":"2",d:"M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"})])],-1)),t[28]||(t[28]=e("div",{class:"text-xs text-blue-700 uppercase tracking-wide font-semibold mb-1"},"Tasa Mensual",-1)),e("div",lt,a(i.tasa_mensual.toFixed(2))+"%",1),t[29]||(t[29]=e("div",{class:"text-xs text-blue-600"},"Interés ordinario",-1))]),e("div",mt,[t[30]||(t[30]=e("div",{class:"text-purple-600 mb-2"},[e("svg",{class:"w-8 h-8 mx-auto",fill:"none",stroke:"currentColor",viewBox:"0 0 24 24"},[e("path",{"stroke-linecap":"round","stroke-linejoin":"round","stroke-width":"2",d:"M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"})])],-1)),t[31]||(t[31]=e("div",{class:"text-xs text-purple-700 uppercase tracking-wide font-semibold mb-1"},"Pago Mensual",-1)),e("div",ct,a(m(i.prestamo.pago_periodico)),1),e("div",gt,a(i.pago_mensual_letras),1)]),e("div",pt,[t[32]||(t[32]=e("div",{class:"text-orange-600 mb-2"},[e("svg",{class:"w-8 h-8 mx-auto",fill:"none",stroke:"currentColor",viewBox:"0 0 24 24"},[e("path",{"stroke-linecap":"round","stroke-linejoin":"round","stroke-width":"2",d:"M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"})])],-1)),t[33]||(t[33]=e("div",{class:"text-xs text-orange-700 uppercase tracking-wide font-semibold mb-1"},"Total de Pagos",-1)),e("div",ut,a(i.prestamo.numero_pagos),1),t[34]||(t[34]=e("div",{class:"text-xs text-orange-600"},"Mensualidades",-1))])]),e("div",vt,[e("div",xt,[t[38]||(t[38]=e("h3",{class:"text-lg font-semibold text-gray-900 mb-4 flex items-center"},[e("svg",{class:"w-5 h-5 mr-2 text-gray-600",fill:"none",stroke:"currentColor",viewBox:"0 0 24 24"},[e("path",{"stroke-linecap":"round","stroke-linejoin":"round","stroke-width":"2",d:"M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"})]),d(" Información del Deudor ")],-1)),e("div",bt,[e("div",null,[t[35]||(t[35]=e("div",{class:"text-sm font-medium text-gray-700"},"Nombre completo",-1)),e("div",ft,a(i.cliente.nombre_razon_social),1)]),e("div",null,[t[36]||(t[36]=e("div",{class:"text-sm font-medium text-gray-700"},"Domicilio",-1)),e("div",ht,a(i.cliente.direccion_completa||"Domicilio no especificado"),1)]),e("div",null,[t[37]||(t[37]=e("div",{class:"text-sm font-medium text-gray-700"},"Fecha de Vencimiento",-1)),e("div",yt,a(M.value),1)])])]),e("div",wt,[t[41]||(t[41]=e("h3",{class:"text-lg font-semibold text-gray-900 mb-4 flex items-center"},[e("svg",{class:"w-5 h-5 mr-2 text-gray-600",fill:"none",stroke:"currentColor",viewBox:"0 0 24 24"},[e("path",{"stroke-linecap":"round","stroke-linejoin":"round","stroke-width":"2",d:"M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"})]),d(" Información del Acreedor ")],-1)),e("div",kt,[e("div",null,[t[39]||(t[39]=e("div",{class:"text-sm font-medium text-gray-700"},"Beneficiario",-1)),e("div",Ct,a(i.empresa.nombre),1)]),e("div",null,[t[40]||(t[40]=e("div",{class:"text-sm font-medium text-gray-700"},"Lugar de Pago",-1)),e("div",Ft,a(i.empresa.direccion),1)])])])]),e("div",Dt,[t[50]||(t[50]=e("h3",{class:"text-lg font-semibold text-gray-900 mb-4 flex items-center"},[e("svg",{class:"w-5 h-5 mr-2 text-yellow-600",fill:"none",stroke:"currentColor",viewBox:"0 0 24 24"},[e("path",{"stroke-linecap":"round","stroke-linejoin":"round","stroke-width":"2",d:"M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"})]),d(" Compromiso de Pago ")],-1)),e("p",Et,[t[42]||(t[42]=d(" Yo, ")),e("strong",null,a(i.cliente.nombre_razon_social),1),t[43]||(t[43]=d(", con domicilio en ")),e("strong",null,a(i.cliente.direccion_completa||"Domicilio no especificado"),1),t[44]||(t[44]=d(", me obligo incondicionalmente a pagar a la orden de ")),e("strong",null,a(i.empresa.nombre),1),t[45]||(t[45]=d(" la cantidad de ")),e("strong",null,a(m(i.prestamo.monto_prestado)),1),d(" ($"+a(i.monto_letras)+"), más intereses ordinarios a razón de ",1),e("strong",null,a(i.tasa_mensual.toFixed(2))+"%",1),t[46]||(t[46]=d(" mensual, pagaderos mensualmente junto con cada exhibición de capital. "))]),e("p",Mt,[t[47]||(t[47]=d(" Los pagos se realizarán mensualmente a partir del ")),e("strong",null,a(b.value),1),t[48]||(t[48]=d(", hasta completar un total de ")),e("strong",null,a(i.prestamo.numero_pagos),1),t[49]||(t[49]=d(" pagos, conforme al plan de pagos establecido. "))])]),e("div",Pt,[e("div",$t,[t[51]||(t[51]=e("div",{class:"w-3/4 mx-auto h-16 border-b-2 border-gray-700 mb-4"},null,-1)),e("div",jt,a(i.cliente.nombre_razon_social),1),t[52]||(t[52]=e("div",{class:"text-sm text-gray-600"},"Deudor(a) — Firma autógrafa",-1))]),e("div",_t,[t[53]||(t[53]=e("div",{class:"w-3/4 mx-auto h-16 border-b-2 border-gray-700 mb-4"},null,-1)),e("div",At,a(i.empresa.nombre_comercial)+" "+a(i.empresa.nombre),1),t[54]||(t[54]=e("div",{class:"text-sm text-gray-600"},"Acreedor — Representante legal",-1))])]),t[56]||(t[56]=h('<div class="bg-blue-50 border border-blue-200 rounded-xl p-6 text-center" data-v-ce9aa003><div class="flex items-center justify-center mb-4" data-v-ce9aa003><svg class="w-8 h-8 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" data-v-ce9aa003><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" data-v-ce9aa003></path></svg><h3 class="text-lg font-semibold text-blue-900" data-v-ce9aa003>Información Importante</h3></div><p class="text-blue-800 mb-4" data-v-ce9aa003> Este documento constituye un título ejecutivo que faculta al tenedor legítimo para exigir el pago por la vía ejecutiva correspondiente. El incumplimiento generará intereses moratorios y podrá resultar en el vencimiento anticipado de la obligación. </p><div class="flex items-center justify-center gap-4 text-sm text-blue-700" data-v-ce9aa003><span data-v-ce9aa003>✓ Documento Legal Válido</span><span data-v-ce9aa003>✓ Protegido por Legislación Mexicana</span><span data-v-ce9aa003>✓ Confidencial y Privado</span></div></div>',1)),e("div",Nt,[t[55]||(t[55]=e("p",{class:"text-sm text-gray-500"}," Esta es una vista previa • El documento PDF oficial incluye formato A4 con encabezado y pie de página profesionales ",-1)),e("p",zt," Generado el "+a(D(new Date))+" • Versión "+a(x.version),1)])])])]),e("div",Rt,[e("div",Ot,[e("div",Tt,[e("div",St,[t[58]||(t[58]=e("label",{class:"text-sm font-medium text-gray-700"},"Tamaño:",-1)),W(e("select",{"onUpdate:modelValue":t[3]||(t[3]=n=>c.value=n),class:"border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"},t[57]||(t[57]=[e("option",{value:"carta"},'📄 Carta (8.5" × 11")',-1),e("option",{value:"oficio"},'📃 Oficio (8.5" × 13")',-1),e("option",{value:"a4"},"📋 A4 (210mm × 297mm)",-1)]),512),[[ee,c.value]])]),e("button",{onClick:t[4]||(t[4]=n=>w(c.value)),class:"inline-flex items-center px-8 py-4 bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white text-lg font-semibold rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"},t[59]||(t[59]=[e("svg",{class:"w-6 h-6 mr-3",fill:"none",stroke:"currentColor",viewBox:"0 0 24 24"},[e("path",{"stroke-linecap":"round","stroke-linejoin":"round","stroke-width":"2",d:"M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"})],-1),d(" Generar Documento PDF Oficial ")])),e("div",Bt,[t[60]||(t[60]=e("div",{class:"font-medium"},"Documento profesional listo para impresión",-1)),e("div",It,"Tamaño seleccionado: "+a((s=y[c.value])==null?void 0:s.nombre)+" • Diseño ejecutivo",1)])])])])])])],64)}}}),Qt=we(Vt,[["__scopeId","data-v-ce9aa003"]]);export{Qt as default};
