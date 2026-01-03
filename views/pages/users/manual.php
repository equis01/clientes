<?php
if(session_status()!==PHP_SESSION_ACTIVE){ session_start(); }
if(!isset($_SESSION['user'])){header('Location: /users/login');exit;}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <?php $pageTitle='Manual de Usuario'; include dirname(__DIR__,2).'/layout/head.php'; ?>
  <style>
    .docs-layout {
      display: flex;
      gap: 30px;
      align-items: flex-start;
    }
    .docs-sidebar {
      width: 250px;
      flex-shrink: 0;
      position: sticky;
      top: 100px;
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: 12px;
      padding: 16px;
    }
    .docs-sidebar h3 {
      font-size: 14px;
      font-weight: 600;
      text-transform: uppercase;
      color: #64748b;
      margin: 0 0 10px 0;
      padding-bottom: 8px;
      border-bottom: 1px solid var(--border);
    }
    .docs-nav {
      list-style: none;
      padding: 0;
      margin: 0;
    }
    .docs-nav li {
      margin-bottom: 4px;
    }
    .docs-nav a {
      display: block;
      padding: 8px 12px;
      border-radius: 6px;
      color: var(--text);
      font-size: 14px;
      text-decoration: none;
      transition: background 0.2s, color 0.2s;
    }
    .docs-nav a:hover, .docs-nav a.active {
      background: rgba(0, 220, 42, 0.1);
      color: #00DC2A;
      font-weight: 500;
    }
    .docs-content {
      flex: 1;
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: 12px;
      padding: 40px;
      min-width: 0; /* Prevent overflow */
    }
    .docs-content h1 {
      margin-top: 0;
      font-size: 28px;
      border-bottom: 1px solid var(--border);
      padding-bottom: 16px;
      margin-bottom: 30px;
    }
    .docs-content h2 {
      font-size: 20px;
      margin-top: 40px;
      margin-bottom: 16px;
      color: var(--text);
    }
    .docs-content p {
      line-height: 1.6;
      color: var(--text);
      opacity: 0.9;
      margin-bottom: 16px;
    }
    .docs-content ul {
      padding-left: 20px;
      margin-bottom: 20px;
      line-height: 1.6;
    }
    .docs-content li {
      margin-bottom: 8px;
    }
    .docs-content img {
      max-width: 100%;
      border-radius: 8px;
      border: 1px solid var(--border);
      margin: 20px 0;
    }
    .alert-info {
      background: rgba(3, 102, 214, 0.1);
      border-left: 4px solid #0366d6;
      padding: 16px;
      border-radius: 4px;
      margin: 20px 0;
    }
    
    /* Estilos Acordeón Soporte */
    details.branch-details {
      margin-bottom: 12px;
      background: var(--header);
      border: 1px solid var(--border);
      border-radius: 8px;
      overflow: hidden;
    }
    details.branch-details summary {
      padding: 14px 16px;
      cursor: pointer;
      font-weight: 600;
      display: flex;
      align-items: center;
      justify-content: space-between;
      list-style: none;
      background: var(--card);
    }
    details.branch-details summary::-webkit-details-marker {
      display: none;
    }
    details.branch-details summary::after {
      content: '';
      width: 10px;
      height: 10px;
      border-right: 2px solid var(--text);
      border-bottom: 2px solid var(--text);
      transform: rotate(-45deg); /* Apunta a la izquierda (hacia el texto si estuviera a la izq, pero aqui esta a la derecha) - Ajustaremos */
      transition: transform 0.2s ease;
      margin-left: 10px;
    }
    /* Ajuste de flecha: Si se cierra es señalando a la palabra (izquierda), si se abre voltea hacia abajo */
    /* Vamos a usar un SVG o border trick mejor posicionado */
    details.branch-details summary::after {
        border: none;
        content: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>');
        transform: rotate(0deg);
    }
    /* User request: "la flechica que si se abre, voltea hacia abajo y si se cierra, es señalando a la palabra" 
       Si el texto está a la izquierda y la flecha a la derecha:
       Cerrado: flecha apunta a la palabra (izquierda) <
       Abierto: flecha apunta abajo v
       
       Pero standard accordion arrow is usually on the right or left. 
       Let's put arrow on the LEFT.
    */
    details.branch-details summary {
        justify-content: flex-start;
        gap: 10px;
    }
    details.branch-details summary::before {
        content: '';
        width: 0; 
        height: 0; 
        border-top: 6px solid transparent;
        border-bottom: 6px solid transparent;
        border-left: 8px solid var(--text); /* Apunta derecha (hacia palabra) */
        transition: transform 0.2s;
    }
    details.branch-details[open] summary::before {
        transform: rotate(90deg); /* Apunta abajo */
    }
    details.branch-details summary::after { content: none; } /* Remove previous try */
    
    .branch-content {
      padding: 16px;
      border-top: 1px solid var(--border);
    }
    .contact-block {
      margin-bottom: 16px;
    }
    .contact-block:last-child {
      margin-bottom: 0;
    }
    .contact-title {
      font-size: 13px;
      text-transform: uppercase;
      color: #64748b;
      font-weight: 700;
      margin-bottom: 8px;
      display: block;
    }
    .contact-item {
      display: flex;
      align-items: center;
      gap: 8px;
      margin-bottom: 6px;
      font-size: 14px;
    }
    .contact-item a {
      color: var(--accent2);
      text-decoration: none;
    }
    .contact-item a:hover {
      text-decoration: underline;
    }
    
    @media (max-width: 768px) {
      .docs-layout {
        flex-direction: column;
      }
      .docs-sidebar {
        width: 100%;
        position: static;
        margin-bottom: 20px;
      }
    }
  </style>
</head>
<body>
  <?php include dirname(__DIR__,2).'/layout/header.php'; ?>
  <main class="container" style="max-width: 1200px;">
    
    <div class="docs-layout">
      <!-- Sidebar de Navegación -->
      <aside class="docs-sidebar">
        <h3>Contenido</h3>
        <ul class="docs-nav">
          <li><a href="#intro" class="active">Bienvenido</a></li>
          <li><a href="#portal">Portal Principal</a></li>
          <li><a href="#servicios">Servicios y Reportes</a></li>
          <li><a href="#finanzas">Finanzas</a></li>
          <li><a href="#config">Configuración</a></li>
          <li><a href="#support">Soporte</a></li>
        </ul>
      </aside>
      
      <!-- Contenido Principal -->
      <article class="docs-content">
        
        <section id="intro">
          <h1>Manual de Usuario</h1>
          <p>Bienvenido al <strong>Portal de Clientes de Medios con Valor</strong>. Este sistema ha sido diseñado para brindarte transparencia total sobre tus servicios de recolección, métricas ambientales y estado financiero en tiempo real.</p>
          <p>En este manual encontrarás toda la información necesaria para navegar por la plataforma y sacar el máximo provecho de sus herramientas.</p>
        </section>

        <section id="portal">
          <h2>Portal Principal</h2>
          <p>Al iniciar sesión, accederás al <strong>Portal</strong>, tu centro de mando. Aquí encontrarás accesos directos a las funciones más importantes:</p>
          <ul>
            <li><strong>Carpeta Integral:</strong> Un enlace directo a tu carpeta en Google Drive donde almacenamos toda tu documentación oficial (manifiestos, contratos, etc.).</li>
            <li><strong>Manual de Usuario:</strong> Acceso a este documento siempre que lo necesites.</li>
          </ul>
          
          <h3>¿Por qué usamos Drive?</h3>
          <p>Por temas administrativos, <strong>Medios con Valor</strong> ha usado Google Workspace para diferentes temas, como bases de datos, sistemas de programación, correos corporativos, etc. Por lo que <strong>Drive</strong> es la nube donde almacenamos la mayor cantidad de información y hacemos uso para resguardar de manera privada la información de cada uno de nuestros clientes.</p>

          <div class="alert-info">
            <strong>Nota:</strong> Para acceder a la Carpeta Integral, es necesario que tu correo electrónico esté vinculado a una cuenta de Google. <br>
            Si no sabes cómo hacerlo, aquí hay un manual que te puede ayudar a crear una cuenta de Google con tu correo empresarial: <a href="/users/google_manual" target="_blank" style="font-weight: 500;">Ver guía paso a paso</a>.
          </div>
        </section>

        <section id="servicios">
          <h2>Servicios</h2>
          <p>En la sección de <strong>Servicios</strong> puedes consultar el historial detallado de recolecciones realizadas.</p>
          
          <h3>Filtrado de Información</h3>
          <p>Utiliza los filtros en la parte superior para buscar información específica:</p>
          <ul>
            <li><strong>Mes:</strong> Selecciona un mes específico o "Todo el año" para ver el acumulado.</li>
            <li><strong>Alias (si aplica):</strong> Se refiere a los diferentes <strong>puntos de recolección</strong> (ubicaciones) registrados bajo tu cuenta, no necesariamente sucursales distintas.</li>
          </ul>
          <div class="alert-info">
            <strong>Nota:</strong> Si tu empresa cuenta con servicios en las 3 sucursales (Monterrey, Aguascalientes, Querétaro), recuerda que el acceso es <strong>un usuario por sucursal</strong>.
          </div>

          <h3>Generación de Reportes</h3>
          <p>Puedes descargar reportes oficiales en formato PDF o Excel presionando el botón <strong>"Generar reporte"</strong>. El sistema procesará la información del mes seleccionado y te entregará un archivo listo para descargar.</p>
          <p><em>Recuerda: El reporte del mes en curso estará disponible a partir del día 4 del mes siguiente.</em></p>
        </section>

        <section id="finanzas">
          <h2>Finanzas</h2>
          <p>El módulo de <strong>Finanzas</strong> te permite mantener el control de tu facturación y consumos.</p>
          <ul>
            <li><strong>Tarifa Actual:</strong> Tu costo por servicio o volumen contratado.</li>
            <li><strong>Volumen Contratado:</strong> La capacidad en m³ incluida en tu plan.</li>
            <li><strong>Excesos:</strong> Si has superado tu volumen contratado, aquí verás el cálculo del excedente.</li>
            <li><strong>Última Factura:</strong> Referencia rápida al folio de tu última factura generada.</li>
          </ul>
          <p>Si tienes dudas sobre cómo se calcula el cobro por exceso, puedes hacer clic en el botón <strong>"¿Cómo se calcula?"</strong> dentro de esta sección.</p>
        </section>

        <section id="config">
          <h2>Configuración</h2>
          <p>En el apartado de <strong>Configuración</strong> puedes gestionar la seguridad de tu cuenta:</p>
          <ul>
            <li><strong>Cambio de Contraseña:</strong> Actualiza tu clave de acceso regularmente para mayor seguridad.</li>
            <li><strong>Información de Perfil:</strong> Por seguridad, no es posible visualizar ni editar la información de perfil desde el portal. <em>(Nota: Contacta a <a href="#support">soporte</a> si requieres cambios).</em></li>
          </ul>
        </section>

        <section id="support">
          <h2>Soporte</h2>
          
          <div class="contact-block" style="margin-bottom: 30px;">
             <span class="contact-title" style="font-size: 1.1em; color: var(--text);">Soporte en plataforma:</span>
             <div class="contact-item">
               <a href="mailto:sistemas@mediosconvalor.com" style="font-weight: 500; color: #00DC2A;">sistemas@mediosconvalor.com</a>
             </div>
          </div>

          <h3>Atención a clientes</h3>
          <p>Selecciona tu sucursal para ver los datos de contacto correspondientes:</p>

          <!-- Monterrey -->
          <details class="branch-details">
            <summary>Monterrey</summary>
            <div class="branch-content">
              <div class="contact-block">
                <span class="contact-title">Teléfono General</span>
                <div class="contact-item">
                  <a href="tel:8113395722">(81) 1339 5722</a>
                </div>
              </div>
              
              <div class="contact-block">
                <span class="contact-title">Temas administrativos y facturación</span>
                <div class="contact-item">
                  <span>• Correo:</span> <a href="mailto:facturasmty@mediosconvalor.com">facturasmty@mediosconvalor.com</a>
                </div>
                <div class="contact-item">
                  <img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg" width="16" height="16" alt="WhatsApp">
                  <a href="https://wa.me/528113395722" target="_blank">WhatsApp: (81) 1339 5722</a>
                </div>
              </div>

              <div class="contact-block">
                <span class="contact-title">Temas de recolección</span>
                <div class="contact-item">
                  <span>• Correo:</span> <a href="mailto:calidadmty@mediosconvalor.com">calidadmty@mediosconvalor.com</a>
                </div>
                <div class="contact-item">
                  <img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg" width="16" height="16" alt="WhatsApp">
                  <a href="https://wa.me/528119096903" target="_blank">WhatsApp: (81) 1909 6903</a>
                </div>
              </div>
            </div>
          </details>

          <!-- Aguascalientes -->
          <details class="branch-details">
            <summary>Aguascalientes</summary>
            <div class="branch-content">
              <div class="contact-block">
                <span class="contact-title">Teléfono General</span>
                <div class="contact-item">
                  <a href="tel:4496888945">449 688 8945</a>
                </div>
              </div>

              <div class="contact-block">
                <span class="contact-title">Temas administrativos y facturación</span>
                <div class="contact-item">
                  <span>• Correo:</span> <a href="mailto:facturasags@mediosconvalor.com">facturasags@mediosconvalor.com</a>
                </div>
                <div class="contact-item">
                  <img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg" width="16" height="16" alt="WhatsApp">
                  <a href="https://wa.me/524492832288" target="_blank">WhatsApp: 449 283 2288</a>
                </div>
              </div>

              <div class="contact-block">
                <span class="contact-title">Temas de operación</span>
                <div class="contact-item">
                  <span>• Correo:</span> <a href="mailto:calidadags@mediosconvalor.com">calidadags@mediosconvalor.com</a>
                </div>
                <div class="contact-item">
                  <img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg" width="16" height="16" alt="WhatsApp">
                  <a href="https://wa.me/524492656569" target="_blank">WhatsApp: 449 265 6569</a>
                </div>
              </div>
            </div>
          </details>

          <!-- Querétaro -->
          <details class="branch-details">
            <summary>Querétaro</summary>
            <div class="branch-content">
              <div class="contact-block">
                <span class="contact-title">Temas administrativos y facturación</span>
                <div class="contact-item">
                  <span>• Correo:</span> <a href="mailto:facturasqro@mediosconvalor.com">facturasqro@mediosconvalor.com</a>
                </div>
                <div class="contact-item">
                  <img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg" width="16" height="16" alt="WhatsApp">
                  <a href="https://wa.me/524424710760" target="_blank">WhatsApp: 442 471 0760</a>
                </div>
              </div>

              <div class="contact-block">
                <span class="contact-title">Temas de operación</span>
                <div class="contact-item">
                  <span>• Correo:</span> <a href="mailto:calidadqro@mediosconvalor.com">calidadqro@mediosconvalor.com</a>
                </div>
                <div class="contact-item">
                  <img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg" width="16" height="16" alt="WhatsApp">
                  <a href="https://wa.me/524461385019" target="_blank">WhatsApp: 446 138 5019</a>
                </div>
              </div>
            </div>
          </details>

        </section>

      </article>
    </div>

  </main>
  
  <script>
    // Script para resaltar la sección activa en el sidebar al hacer scroll
    document.addEventListener('DOMContentLoaded', function() {
      const sections = document.querySelectorAll('section');
      const navLinks = document.querySelectorAll('.docs-nav a');
      
      window.addEventListener('scroll', () => {
        let current = '';
        sections.forEach(section => {
          const sectionTop = section.offsetTop;
          const sectionHeight = section.clientHeight;
          if (pageYOffset >= (sectionTop - 150)) {
            current = section.getAttribute('id');
          }
        });
        
        navLinks.forEach(link => {
          link.classList.remove('active');
          if (link.getAttribute('href').includes(current)) {
            link.classList.add('active');
          }
        });
      });

      // Smooth scroll
      document.querySelectorAll('.docs-nav a').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
          e.preventDefault();
          document.querySelector(this.getAttribute('href')).scrollIntoView({
            behavior: 'smooth'
          });
        });
      });
    });
  </script>

  <?php include dirname(__DIR__,2).'/layout/footer.php'; ?>
</body>
</html>