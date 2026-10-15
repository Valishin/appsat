// SWIPER
import Swiper from 'swiper'
import { Navigation, EffectFade, Autoplay, Pagination } from 'swiper/modules'

// GSAP
import { gsap } from "gsap"
import { ScrollTrigger } from "gsap/ScrollTrigger"
import { SplitText } from "gsap/SplitText"
import { ScrollSmoother } from "gsap/ScrollSmoother"
import L from "leaflet";
import SignaturePad from 'signature_pad';

gsap.registerPlugin(ScrollTrigger, ScrollSmoother, SplitText)

const av_smooth_scroller_init = () => {
    // ScrollSmoother.create({
    //     smooth: 1.6,   // seconds it takes to "catch up" to native scroll position
    //     effects: true, // look for data-speed and data-lag attributes on elements and animate accordingly
    //     ignoreMobileResize: false,
    //     smoothTouch: true
    // });
}

window.requestAnimationFrame = (() => {
    return window.requestAnimationFrame ||
        function(callback) {
            window.setTimeout(callback, 1000 / 60);
        };
})();

//  CUSTOM JS


    // --- GLOBAL VARS ---------------------------- 

        // SCREENSIZE
        let w = window,
            d = document,
            e = d.documentElement,
            g = document.body,
            x = w.innerWidth || e.clientWidth || (g && g.clientWidth),
            y = w.innerHeight|| e.clientHeight || (g && g.clientHeight);

        let isTouch = (('ontouchstart' in window) || (navigator.msMaxTouchPoints > 0) || (navigator.maxTouchPoints));

        let top_display = 300;

    // END GLOBAL VARS -----------------------------




    // --- GLOBAL FUNCTIONS ---------------------------- 

    // AV CALL FN -- SHORTHAND
    window.av_call_fn = (selector, fn, args) => { if( document.querySelectorAll(selector).length>0 ) fn(args); }

    const getDeviceType = () => {
        const ua = navigator.userAgent;

        if (/Mobi|iPhone|Android.+Mobile/.test(ua)) {
            return "mobile";
        } else if (/Tablet|iPad|Android(?!.*Mobile)/.test(ua)) {
            return "tablet";
        }

        // fallback por tamaño
        const width = window.innerWidth;
        if (width <= 610) return "mobile";
        if (width <= 1190) return "tablet";
        return "desktop";
    }

    const debugger_tool = () =>{

        const debuggerBtn = document.querySelector('.js-debugger-tool')
        if (!debuggerBtn) return

        debuggerBtn.addEventListener('click', () => {
            const debug = document.querySelector('.js-body')
            
            if(debug.classList.contains('is-debug')) return debug.classList.remove('is-debug')                
             
            debug.classList.add('is-debug')
        })

    }

    const av_remove_loader = () => {

        const jsLoader = document.querySelector('.js-loader')

        new gsap.timeline()
            // .addSpace("+=0.2")
            .to( {}, { duration: 0.2 } )
                .call( () => {
                    debugger_tool();                    
                    av_start_funcs();
                })
            // .addSpace("+=0.2")
                .call( () => {
                    // scrollbar.scrollTo(0, 0, 200);
                })
            .addLabel('start') 
                .to(
                    jsLoader,
                    0.4,
                    {
                        opacity: 0,
                        ease: "power1.out"
                    },
                    'start'
                )
                .call( () => {
                    jsLoader.remove();                   
                })
            ;

    }

    // EXAMPLE: av_set_varcss('--my-var', my_value + 'px');
    const av_set_varcss = (property, value) => {

        let html = document.getElementsByTagName('html')[0];
        html.style.setProperty(property, value);

    }

    let all_slider = []

    const av_slider = () => {
        all_slider = []

        const getAllSliders = document.querySelectorAll('.js-slider')

        getAllSliders.forEach(item => {

            var current_selector    = item.querySelector('.js-swiper__swiper');           

            var swiper = new Swiper( current_selector , {    
                modules: [Navigation, Autoplay, EffectFade, Pagination],                            
                slidesPerView: 1,
                spaceBetween: 15,
                loop: true,
                centeredSlides: true,                                                                                                              
                effect: 'fade',   
                pagination: {
                    el: ".swiper-pagination",
                    clickable: true,
                },
                navigation:{
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev'
                }                                                                                        
            });

            all_slider.push(swiper);

        });
    }

    const av_reset_vars_css = () => {
       
        let _html = document.getElementsByTagName('html')[0];
        let _col_1 = document.querySelectorAll('.js-col-1')[0].offsetWidth;
        let _col_1_inner = document.querySelector('.js-col-1-inner').innerWidth;
        let _header_height = document.querySelector('.js-header').innerHeight;

        _html.style.setProperty('--col-1', _col_1 + 'px');
        _html.style.setProperty('--col-1-inner', _col_1_inner + 'px');
        _html.style.setProperty('--header-height', Math.round(_header_height) + 'px');

        // ? https://css-tricks.com/the-trick-to-viewport-units-on-mobile/
        // First we get the viewport height and we multiple it by 1% to get a value for a vh unit
        let vh = window.innerHeight * 0.01;
        // Then we set the value in the --vh custom property to the root of the document
        _html.style.setProperty('--vh', `${vh}px`);

    }

    const av_global_scroll = () => {

        let smoothScrollClass = false;
        // let smoothScrollClass = '.js-smooth-scroll'; // ! APPLY THE SMOOTH SCROLL ON WEBSITE

        let scroller = smoothScrollClass ? smoothScrollClass : window;
        let trigger = smoothScrollClass ? smoothScrollClass : 'body';

        ScrollTrigger.defaults({
            scroller: scroller
        });

        // GLOBAL SCROLL
        ScrollTrigger.create({
            trigger: trigger,
            start: "top top",
            onUpdate: (self) => {

                // ? https://codepen.io/theophileavoyne/pen/poNVyzE
                // ? https://greensock.com/forums/topic/26554-keep-positionprogress-of-scrub-animation-on-resize/
                let progressInPx = self.progress * ((self.end + y) - self.start);
            
                const jsHeaderNode = document.querySelector('.js-header')
                
                if ( (self.direction==1) && (progressInPx > top_display) ){
                    // downscroll code
                    jsHeaderNode.classList.add('has-transform');
                    jsHeaderNode.classList.add('is-alt');   
                } else {
                    // upscroll code
                    jsHeaderNode.classList.remove('has-transform');
                    if ( (progressInPx <= top_display) ){
                        jsHeaderNode.classList.remove('is-alt');
                    }
                }

            }
        });

        const nodeVideoPin = document.querySelector('.js-single-cpt-themes__video-pin')
        if(nodeVideoPin){
            const nodeSingleThemesImage = document.querySelector('.c-single-cpt-themes__wrapper-video')  
            if(getDeviceType()=='desktop'){
                ScrollTrigger.create({
                    trigger: nodeSingleThemesImage,
                    start: "top top+=100px",       // Cuando el top del contenedor llega al top del viewport
                    end: "bottom top+=100px",   // Cuando el bottom del contenedor llega al bottom del viewport
                    pin: nodeVideoPin,         // Hace "pin" de la imagen (se queda fija)
                    pinSpacing: true,
                    // markers: true
                })        
            }     
        }

        ScrollTrigger.batch(".js-anim-inview", {
            toggleClass: "is-inview",
            start: "top+=200px bottom",
            // end: () => "+=" + 50,
            // markers: true,
            once: true,
            id: "is-inview"
        });

    }

    const av_gallery_image = () => {

        document.querySelectorAll('.js-gallery__wrapper-image').forEach(e => {
            e.addEventListener('click', i => {
                const imageBig = document.querySelector('.c-gallery__image-big')                
                if(imageBig){
                    imageBig.remove()
                }

                document.querySelector('.c-gallery__velo').classList.add('is-active')

                const currentSrc = i.target.src
                const parentDiv = document.querySelector('.js-gallery__velo-image')
                const imgElem = new Image()
                
                imgElem.src = currentSrc
                imgElem.classList.add('c-gallery__image-big')
                parentDiv.appendChild(imgElem)
            })

        })

    }

    const av_gallery_remove_image = () => {

        document.querySelector('.js-gallery__remove-image').addEventListener('click', () => {
            document.querySelector('.c-gallery__velo').classList.remove('is-active')
            const imageBig = document.querySelector('.c-gallery__image-big')
            if(imageBig){
                imageBig.remove()
            }
        })

    }

    const toggle_menu = () => {
        const body = document.querySelector('body')
        const isActive = body.classList.toggle('is-dropdown-active')

        // Con el menú lateral abierto en móvil, el contenido de detrás no scrollea
        body.classList.toggle('is-overflow-hidden', isActive)
    }

    const close_menu = () => {
        const body = document.querySelector('body')
        if (!body.classList.contains('is-dropdown-active')) return

        body.classList.remove('is-dropdown-active')
        body.classList.remove('is-overflow-hidden')
    }

    // Plegar/desplegar el menú lateral a solo iconos. La clase la pone también
    // un script en la cabecera antes de pintar, para que no haya salto al cargar.
    const av_header_collapse = () => {

        const btn  = document.querySelector('.js-header__collapse')
        const root = document.documentElement

        if (!btn) return

        const syncBtn = () => {
            const plegado = root.classList.contains('is-sidebar-collapsed')
            btn.title = plegado ? 'Desplegar menú' : 'Plegar menú'
            btn.setAttribute('aria-expanded', plegado ? 'false' : 'true')
        }

        syncBtn()

        btn.addEventListener('click', () => {
            const plegado = root.classList.toggle('is-sidebar-collapsed')

            // La preferencia manda sobre el ajuste automático por ancho
            try {
                localStorage.setItem('av_sidebar_collapsed', plegado ? '1' : '0')
            } catch (e) {}

            syncBtn()
        })

    }

    // Plegar/desplegar cada bloque (acordeón) del formulario de SAT y del de
    // Artículo — comparten la misma clase .c-sat-form__accordion. El estado
    // se recuerda en localStorage por título de bloque (no por SAT/artículo
    // concreto): así "Proveedor" empieza plegado en todas las fichas si lo
    // plegaste una vez, igual que el propio menú lateral recuerda si está
    // plegado o no.
    const av_accordion_collapse = () => {

        document.querySelectorAll('.c-sat-form__accordion').forEach(acc => {

            const title = acc.querySelector('.c-sat-form__accordion-title')
            if (!title) return

            const key = 'av_accordion_collapsed_' + title.textContent.trim()

            try {
                if (localStorage.getItem(key) === '1') acc.classList.add('is-collapsed')
            } catch (e) {}

            title.addEventListener('click', () => {
                const plegado = acc.classList.toggle('is-collapsed')
                try {
                    localStorage.setItem(key, plegado ? '1' : '0')
                } catch (e) {}
            })

        })

    }

    // Buscador de SAT fijo en el sidebar (de momento solo por número). Con el
    // menú desplegado el campo ya está visible; plegado, solo se ve la lupa y
    // pulsarla revela el campo como un cuadro flotante.
    // Dashboard: cambiar el mes/año del selector recarga la página con esos
    // valores como parámetros GET (los calcula/valida el propio PHP).
    const av_dashboard_month_picker = () => {
        document.querySelectorAll('.js-dashboard-month-select').forEach(select => {
            select.addEventListener('change', () => {
                select.closest('form')?.submit()
            })
        })
    }

    // Gráfico de evolución histórica del dashboard: barras SVG dibujadas a mano
    // (sin librería nueva) a partir de los datos ya calculados en PHP y
    // embebidos en data-chart. El desplegable solo cambia qué serie de las ya
    // traídas se pinta, sin volver a pedir nada al servidor.
    const av_dashboard_chart = () => {

        const container = document.querySelector('.js-dashboard-chart')
        if (!container) return

        let data
        try {
            data = JSON.parse(container.dataset.chart || '{}')
        } catch (e) {
            return
        }

        const svg      = container.querySelector('.js-dashboard-chart-svg')
        const emptyEl  = container.querySelector('.js-dashboard-chart-empty')
        const select   = document.querySelector('.js-dashboard-chart-metric')
        const SVG_NS   = 'http://www.w3.org/2000/svg'

        const formatValue = (metric, value) => {
            if (metric === 'ingresos') {
                return value.toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' €'
            }
            return String(value)
        }

        // Versión compacta (sin decimales) para la etiqueta que va encima de
        // cada barra: el detalle exacto ya se ve en el tooltip al pasar el ratón.
        const formatValueCompact = (metric, value) => {
            if (metric === 'ingresos') {
                return Math.round(value).toLocaleString('es-ES') + ' €'
            }
            return String(value)
        }

        // Redondea el máximo del eje a un número "bonito" (1/2/5 x 10^n) para
        // que las líneas horizontales queden en valores legibles (0, 5, 10...).
        const niceMax = (value) => {
            if (value <= 0) return 1
            const magnitude = Math.pow(10, Math.floor(Math.log10(value)))
            const residual  = value / magnitude
            let niceResidual
            if (residual <= 1) niceResidual = 1
            else if (residual <= 2) niceResidual = 2
            else if (residual <= 5) niceResidual = 5
            else niceResidual = 10
            return niceResidual * magnitude
        }

        // Rectángulo con las esquinas superiores redondeadas y las inferiores
        // en ángulo recto (apoyadas en el eje), como una barra de verdad.
        const barPath = (x, y, w, h, r) => {
            const radius = Math.min(r, w / 2, h)
            if (radius <= 0) {
                return `M ${x},${y + h} L ${x},${y} L ${x + w},${y} L ${x + w},${y + h} Z`
            }
            return `M ${x},${y + h}
                    L ${x},${y + radius}
                    Q ${x},${y} ${x + radius},${y}
                    L ${x + w - radius},${y}
                    Q ${x + w},${y} ${x + w},${y + radius}
                    L ${x + w},${y + h}
                    Z`
        }

        const render = (metric) => {
            const values = Array.isArray(data[metric]) ? data[metric] : []
            const labels = Array.isArray(data.labels) ? data.labels : []

            while (svg.firstChild) svg.removeChild(svg.firstChild)

            const max = values.length ? Math.max(...values) : 0
            if (!values.length || max <= 0) {
                svg.classList.add('is-hidden')
                emptyEl?.classList.remove('is-hidden')
                return
            }
            svg.classList.remove('is-hidden')
            emptyEl?.classList.add('is-hidden')

            const axisMax       = niceMax(max)
            const numTicks      = 5
            const height        = 260
            const paddingLeft   = 36
            const paddingRight  = 6
            const paddingTop    = 14
            const paddingBottom = 26
            // El viewBox se ajusta al ancho real del contenedor en píxeles (no
            // a un valor arbitrario) para que la escala horizontal y vertical
            // coincidan 1:1: si no, el navegador estira el SVG de forma no
            // uniforme y deforma el texto (se ve "chafado" o "estirado").
            const containerWidth = container.clientWidth || 320
            const minWidth       = values.length * 44
            const width          = Math.max(minWidth, containerWidth)
            const plotWidth     = width - paddingLeft - paddingRight
            const plotHeight    = height - paddingTop - paddingBottom
            const barGap        = Math.max((plotWidth / values.length) * 0.32, 4)
            const barWidth      = (plotWidth / values.length) - barGap
            // Con muchos meses no caben todas las etiquetas: se muestran solo
            // una de cada N para que no se amontonen.
            const labelStep     = Math.max(1, Math.ceil(values.length / 12))

            svg.setAttribute('viewBox', `0 0 ${width} ${height}`)
            svg.setAttribute('preserveAspectRatio', 'xMinYMin meet')
            svg.style.width  = `${width}px`
            svg.style.height = `${height}px`

            // Degradado turquesa→azul compartido por todas las barras.
            const gradientId = `dashboard-chart-gradient-${metric}`
            const defs       = document.createElementNS(SVG_NS, 'defs')
            const gradient   = document.createElementNS(SVG_NS, 'linearGradient')
            gradient.setAttribute('id', gradientId)
            gradient.setAttribute('x1', '0')
            gradient.setAttribute('y1', '1')
            gradient.setAttribute('x2', '0')
            gradient.setAttribute('y2', '0')
            const stopStart = document.createElementNS(SVG_NS, 'stop')
            stopStart.setAttribute('offset', '0%')
            stopStart.setAttribute('class', 'c-dashboard__chart-gradient-start')
            const stopEnd = document.createElementNS(SVG_NS, 'stop')
            stopEnd.setAttribute('offset', '100%')
            stopEnd.setAttribute('class', 'c-dashboard__chart-gradient-end')
            gradient.appendChild(stopStart)
            gradient.appendChild(stopEnd)
            defs.appendChild(gradient)
            svg.appendChild(defs)

            // Líneas horizontales de referencia + etiquetas del eje Y.
            for (let t = 0; t <= numTicks; t++) {
                const tickValue = (axisMax / numTicks) * t
                const y = paddingTop + plotHeight - (tickValue / axisMax) * plotHeight

                const gridLine = document.createElementNS(SVG_NS, 'line')
                gridLine.setAttribute('x1', paddingLeft)
                gridLine.setAttribute('x2', width - paddingRight)
                gridLine.setAttribute('y1', y)
                gridLine.setAttribute('y2', y)
                gridLine.setAttribute('class', 'c-dashboard__chart-grid')
                svg.appendChild(gridLine)

                const tickLabel = document.createElementNS(SVG_NS, 'text')
                tickLabel.setAttribute('x', paddingLeft - 8)
                tickLabel.setAttribute('y', y + 3)
                tickLabel.setAttribute('text-anchor', 'end')
                tickLabel.setAttribute('class', 'c-dashboard__chart-axis-label')
                tickLabel.textContent = Math.round(tickValue).toLocaleString('es-ES')
                svg.appendChild(tickLabel)
            }

            values.forEach((val, i) => {
                const barHeight = axisMax > 0 ? (val / axisMax) * plotHeight : 0
                const x = paddingLeft + i * (barWidth + barGap)
                const y = paddingTop + plotHeight - barHeight

                const path = document.createElementNS(SVG_NS, 'path')
                path.setAttribute('d', barPath(x, y, Math.max(barWidth, 1), Math.max(barHeight, 0), 6))
                path.setAttribute('fill', `url(#${gradientId})`)
                path.setAttribute('class', 'c-dashboard__chart-bar')

                const title = document.createElementNS(SVG_NS, 'title')
                title.textContent = `${labels[i] || ''}: ${formatValue(metric, val)}`
                path.appendChild(title)

                svg.appendChild(path)

                // Total dentro de la barra, pegado abajo, siempre visible (no
                // hace falta pasar el ratón por encima para verlo).
                if (val > 0) {
                    const baseline = paddingTop + plotHeight
                    const valueLabel = document.createElementNS(SVG_NS, 'text')
                    valueLabel.setAttribute('x', x + barWidth / 2)
                    valueLabel.setAttribute('y', baseline - 9)
                    valueLabel.setAttribute('text-anchor', 'middle')
                    valueLabel.setAttribute('class', 'c-dashboard__chart-value')
                    valueLabel.textContent = formatValueCompact(metric, val)
                    svg.appendChild(valueLabel)
                }

                if (i % labelStep === 0) {
                    const text = document.createElementNS(SVG_NS, 'text')
                    text.setAttribute('x', x + barWidth / 2)
                    text.setAttribute('y', height - 8)
                    text.setAttribute('text-anchor', 'middle')
                    text.setAttribute('class', 'c-dashboard__chart-label')
                    text.textContent = labels[i] || ''
                    svg.appendChild(text)
                }
            })
        }

        select?.addEventListener('change', () => render(select.value))
        render(select ? select.value : 'sats')

        // Si cambia el ancho del contenedor (redimensionar ventana, sidebar
        // que se pliega/despliega...) se vuelve a calcular el viewBox para
        // que siga sin deformarse.
        let resizeTimeout
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimeout)
            resizeTimeout = setTimeout(() => render(select ? select.value : 'sats'), 150)
        })
    }

    const av_header_sat_search = () => {

        const wrapper  = document.querySelector('.js-header-search')
        if (!wrapper) return

        const toggleBtn = wrapper.querySelector('.js-header-search-toggle')
        const input      = wrapper.querySelector('.js-header-search-input')
        const feedback   = wrapper.querySelector('.js-header-search-feedback')
        const isCollapsed = () => document.documentElement.classList.contains('is-sidebar-collapsed')

        const closeOverlay = () => {
            wrapper.classList.remove('is-open')
            feedback.textContent = ''
        }

        toggleBtn.addEventListener('click', () => {
            if (!isCollapsed()) {
                input.focus()
                return
            }
            const abrir = !wrapper.classList.contains('is-open')
            wrapper.classList.toggle('is-open', abrir)
            if (abrir) {
                input.focus()
            } else {
                feedback.textContent = ''
            }
        })

        document.addEventListener('click', (e) => {
            if (isCollapsed() && wrapper.classList.contains('is-open') && !wrapper.contains(e.target)) {
                closeOverlay()
            }
        })

        const buscar = () => {
            const numero = input.value.trim()
            if (!numero) return

            feedback.textContent = 'Buscando...'

            const formData = new FormData()
            formData.append('action', 'av_ajax_find_sat_by_number')
            formData.append('nonce', av_data.nonce_sat_search)
            formData.append('numero', numero)

            fetch(av_data.av_ajax_url, { method: 'POST', body: formData })
                .then(res => res.json())
                .then(json => {
                    if (json.success && json.data && json.data.url) {
                        window.location.href = json.data.url
                    } else {
                        feedback.textContent = (json.data && typeof json.data === 'string')
                            ? json.data
                            : `No se ha encontrado el SAT nº ${numero}.`
                    }
                })
                .catch(() => {
                    feedback.textContent = 'No se ha podido buscar. Inténtalo de nuevo.'
                })
        }

        input.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                input.blur()
                closeOverlay()
                return
            }
            if (e.key !== 'Enter') return
            e.preventDefault()
            buscar()
        })

        input.addEventListener('input', () => {
            feedback.textContent = ''
        })

    }

    const av_header_hamburguer = () => {

        const hamburguer = document.querySelector('.js-header__hamburguer')
        const overlay    = document.querySelector('.js-header__overlay')

        hamburguer.addEventListener('click', () =>{
            toggle_menu()
        })

        // Tocar fuera del panel o pulsar Escape lo cierra
        if (overlay) {
            overlay.addEventListener('click', () => close_menu())
        }

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') close_menu()
        })

    }

    const av_generic_velo_close = () => {

        const hamburguer = document.querySelector('.js-generic-velo')

        hamburguer.addEventListener('click', () =>{
            toggle_menu()
        })

    }

    const av_contact_hover_image = () => {

        const nodeClass = document.querySelectorAll('.js-c-contact__wrapper-image')

        nodeClass.forEach(e => {
            e.addEventListener("mouseover", () => {
                e.classList.add('is-hover')
            });
              
            e.addEventListener("mouseout", () => {
                e.classList.remove('is-hover')
            });
        })

    }

    const av_hover = () => {

        const nodeClass = document.querySelectorAll('.js-hover')
        const parentNode = document.querySelector('.o-main')

        nodeClass.forEach(e => {
            const getDataClass = e.getAttribute('data-hover')
            e.addEventListener("mouseover", () => {
                parentNode.classList.add(getDataClass)
            });
              
            e.addEventListener("mouseout", () => {
                parentNode.classList.remove(getDataClass)
            });
        })

    }

const av_split_text_anim = () => {

    const nodeAnim = document.querySelectorAll('.js-split-text')

    nodeAnim.forEach(e => {
        ScrollTrigger.batch(e, {
        onEnter: () => {
            var tl = gsap.timeline()

            var mySplitText = new SplitText(e, { type: "lines" })

            // 👇 Oculta el overflow de cada línea
            mySplitText.lines.forEach(line => {
            line.style.overflow = "hidden";
            });

            // 👇 Anima las líneas desde abajo
            tl.from(mySplitText.lines, {
            duration: 1,
            y: 80,
            ease: "power3.out",
            // stagger: 0.1,
            });
        },
        once: true
        })
    })

}

    const av_image_anim = () => {

        ScrollTrigger.batch('.js-anim-image', {
            start: "top 75%",
            once: true,
            onEnter: (e) => {                
                gsap.fromTo(e, {
                    opacity: 0, 
                    scale: 1.2                                                                                          
                }, {                    
                    scale: 1,
                    opacity: 1,
                    duration: 0.6,
                    ease: "power1.inOut",
                });
            }
        })
        

    }

    const av_open_contact = () => {

        const node = document.querySelectorAll('.js-open-contact')
        const contactToggle = document.querySelector('.b-contact')

        node.forEach(e => {
            e.addEventListener('click', () => {
                if(contactToggle.classList.contains('is-active')){
                    contactToggle.classList.remove('is-active')
                    return
                }
                contactToggle.classList.add('is-active')
            })
        })

    }

    const av_close_contact = () => {

        const node = document.querySelector('.js-b-contact__close')
        const contactToggle = document.querySelector('.b-contact')

        node.addEventListener('click', () => {

            if(!contactToggle.classList.contains('is-active')) return
    
            contactToggle.classList.remove('is-active')
        })


    }

    const av_footer_map = () => {
        const nodeMap = document.querySelector('#map')

        const coordinates = [41.78334547704238, 3.0361138546487214]

        if(!nodeMap) return

        var map = L.map('map').setView(coordinates, 17);

        var myIcon = L.icon({
            iconUrl: 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABkAAAApCAYAAADAk4LOAAAFgUlEQVR4Aa1XA5BjWRTN2oW17d3YaZtr2962HUzbDNpjszW24mRt28p47v7zq/bXZtrp/lWnXr337j3nPCe85NcypgSFdugCpW5YoDAMRaIMqRi6aKq5E3YqDQO3qAwjVWrD8Ncq/RBpykd8oZUb/kaJutow8r1aP9II0WmLKLIsJyv1w/kqw9Ch2MYdB++12Onxee/QMwvf4/Dk/Lfp/i4nxTXtOoQ4pW5Aj7wpici1A9erdAN2OH64x8OSP9j3Ft3b7aWkTg/Fm91siTra0f9on5sQr9INejH6CUUUpavjFNq1B+Oadhxmnfa8RfEmN8VNAsQhPqF55xHkMzz3jSmChWU6f7/XZKNH+9+hBLOHYozuKQPxyMPUKkrX/K0uWnfFaJGS1QPRtZsOPtr3NsW0uyh6NNCOkU3Yz+bXbT3I8G3xE5EXLXtCXbbqwCO9zPQYPRTZ5vIDXD7U+w7rFDEoUUf7ibHIR4y6bLVPXrz8JVZEql13trxwue/uDivd3fkWRbS6/IA2bID4uk0UpF1N8qLlbBlXs4Ee7HLTfV1j54APvODnSfOWBqtKVvjgLKzF5YdEk5ewRkGlK0i33Eofffc7HT56jD7/6U+qH3Cx7SBLNntH5YIPvODnyfIXZYRVDPqgHtLs5ABHD3YzLuespb7t79FY34DjMwrVrcTuwlT55YMPvOBnRrJ4VXTdNnYug5ucHLBjEpt30701A3Ts+HEa73u6dT3FNWwflY86eMHPk+Yu+i6pzUpRrW7SNDg5JHR4KapmM5Wv2E8Tfcb1HoqqHMHU+uWDD7zg54mz5/2BSnizi9T1Dg4QQXLToGNCkb6tb1NU+QAlGr1++eADrzhn/u8Q2YZhQVlZ5+CAOtqfbhmaUCS1ezNFVm2imDbPmPng5wmz+gwh+oHDce0eUtQ6OGDIyR0uUhUsoO3vfDmmgOezH0mZN59x7MBi++WDL1g/eEiU3avlidO671bkLfwbw5XV2P8Pzo0ydy4t2/0eu33xYSOMOD8hTf4CrBtGMSoXfPLchX+J0ruSePw3LZeK0juPJbYzrhkH0io7B3k164hiGvawhOKMLkrQLyVpZg8rHFW7E2uHOL888IBPlNZ1FPzstSJM694fWr6RwpvcJK60+0HCILTBzZLFNdtAzJaohze60T8qBzyh5ZuOg5e7uwQppofEmf2++DYvmySqGBuKaicF1blQjhuHdvCIMvp8whTTfZzI7RldpwtSzL+F1+wkdZ2TBOW2gIF88PBTzD/gpeREAMEbxnJcaJHNHrpzji0gQCS6hdkEeYt9DF/2qPcEC8RM28Hwmr3sdNyht00byAut2k3gufWNtgtOEOFGUwcXWNDbdNbpgBGxEvKkOQsxivJx33iow0Vw5S6SVTrpVq11ysA2Rp7gTfPfktc6zhtXBBC+adRLshf6sG2RfHPZ5EAc4sVZ83yCN00Fk/4kggu40ZTvIEm5g24qtU4KjBrx/BTTH8ifVASAG7gKrnWxJDcU7x8X6Ecczhm3o6YicvsLXWfh3Ch1W0k8x0nXF+0fFxgt4phz8QvypiwCCFKMqXCnqXExjq10beH+UUA7+nG6mdG/Pu0f3LgFcGrl2s0kNNjpmoJ9o4B29CMO8dMT4Q5ox8uitF6fqsrJOr8qnwNbRzv6hSnG5wP+64C7h9lp30hKNtKdWjtdkbuPA19nJ7Tz3zR/ibgARbhb4AlhavcBebmTHcFl2fvYEnW0ox9xMxKBS8btJ+KiEbq9zA4RthQXDhPa0T9TEe69gWupwc6uBUphquXgf+/FrIjweHQS4/pduMe5ERUMHUd9xv8ZR98CxkS4F2n3EUrUZ10EYNw7BWm9x1GiPssi3GgiGRDKWRYZfXlON+dfNbM+GgIwYdwAAAAASUVORK5CYII=',
            iconSize: [24,36],
            iconAnchor: [12,36]
        })

        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
        
        L.marker(coordinates, {icon: myIcon}).addTo(map)            
    }

    const av_video_toggle = () => {

        const videoNode = document.querySelectorAll('.js-video__toggle-scroll')

        videoNode.forEach(e => {
            ScrollTrigger.batch(e,{
                onEnter: () => e.play(),
                onEnterBack: () => e.play(),
                onLeave: () => e.pause(),
                onLeaveBack: () => e.pause()
            })
        })

    }

    const av_menu_images = () => {

        const node = document.querySelectorAll('.c-menu__cta')

        node.forEach(e => {
            const parentDiv = e.closest('.c-menu__wrapper-menu')

            e.addEventListener('mouseenter', ()=>{
                parentDiv.classList.add('is-hover')
            })

            e.addEventListener('mouseleave', ()=> {
                parentDiv.classList.remove('is-hover')
            })
        })

    }

    const av_footer_icons = () => {

        const nodes = document.querySelectorAll('.js-footer-button')

        nodes.forEach(e => {
            const getForData = e.innerText.toLowerCase()            

            e.addEventListener('mouseenter', ()=>{
                const dataName = document.querySelector(`[data-icon="${getForData}"]`)
                dataName.classList.add('is-hover')
            })

            e.addEventListener('mouseleave', ()=> {
                const dataName = document.querySelector(`[data-icon="${getForData}"]`)
                dataName.classList.remove('is-hover')
            })
        })

    }

    const av_menu_order_images = () => {

        const nodes = document.querySelectorAll('.c-menu__bg-wrapper-image')
        const varCss = 'var(--container-padding)'

        nodes.forEach((e, i) => {
            i % 2 === 0 ? e.style.left = varCss : e.style.right = varCss
        })

    }

    const av_single_cpt_themes_video_play = () => {

        const videoNode = document.querySelector('.c-single-cpt-themes__video')
        const nodePlay = document.querySelector('.c-single-cpt-themes__image-icon--play')
        const nodePause = document.querySelector('.c-single-cpt-themes__image-icon--pause')

        document.querySelector('.js-single-cpt-themes__video-play').addEventListener('click', n => {
            if(videoNode.paused){
                videoNode.play()
                nodePause.classList.remove('hide')
                nodePlay.classList.add('hide')
            }else{
                videoNode.pause()
                nodePlay.classList.remove('hide')
                nodePause.classList.add('hide')
            }
        })

    }

    const av_hover_node = () => {

        const nodeClass = document.querySelectorAll('.js-hover-node')        

        nodeClass.forEach(e => {           
            const parentNode = e.parentNode
            e.addEventListener("mouseover", () => {
                parentNode.classList.add('is-hover')
            });
              
            e.addEventListener("mouseout", () => {
                parentNode.classList.remove('is-hover')
            });
        })

    }

    const av_sat_form__repair_date = () => {
        const estadoSelect = document.querySelector('[name="estado"]');
        const repairDateInput = document.querySelector('[name="repair-date"]');
        if (!estadoSelect || !repairDateInput) return;

        const fillDate = () => {
            if (estadoSelect.value === 'reparado' && !repairDateInput.value) {
                const now = new Date();
                const dd  = String(now.getDate()).padStart(2, '0');
                const mm  = String(now.getMonth() + 1).padStart(2, '0');
                const yyyy = now.getFullYear();
                const hh  = String(now.getHours()).padStart(2, '0');
                const min = String(now.getMinutes()).padStart(2, '0');
                repairDateInput.value = `${dd}/${mm}/${yyyy} ${hh}:${min}`;
            }
        };

        fillDate();
        estadoSelect.addEventListener('change', fillDate);
    };

    const av_sat_form__equipment = () => {

        const nodeSelect = document.querySelector('.js-sat-form__type-equipment')

        const nodeSim = document.querySelector('.c-sat-form__input--sim')
        const nodeOther = document.querySelector('.c-sat-form__input--other')
        
        if(nodeSim.value.trim() !== '' && nodeSelect.value==='movil'){
            document.querySelector('.c-sat-form__wrapper-input--sim').classList.remove('is-hidden')
        }

        if(nodeOther.value.trim() !== '' && nodeSelect.value==='otro'){
            document.querySelector('.c-sat-form__wrapper-input--other').classList.remove('is-hidden')
        }

        nodeSelect.addEventListener('change', () => {
            const selectedValue = nodeSelect.value
            document.querySelectorAll('.c-sat-form__wrapper-input--hidden').forEach(e => {
                e.classList.add('is-hidden')
            })                
            if(selectedValue==='otro'){
                document.querySelector('.c-sat-form__wrapper-input--other').classList.remove('is-hidden')
            }else if(selectedValue==='movil'){
                document.querySelector('.c-sat-form__wrapper-input--sim').classList.remove('is-hidden')
            }
        })   

    }

    // Flecha del select: oscura para las pastillas claras, blanca para "Finalizado"
    // (único estado con fondo sólido oscuro).
    const AV_STATUS_CHEVRON_DARK  = "url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E\")"

    const av_enable_button_save_status = () => {

        const nodeSelect = document.querySelectorAll('.js-list-cpt-sats__select-status')
        nodeSelect.forEach(select => {

            // Pinta el propio select como una pastilla de color (antes se
            // coloreaba toda la celda, mucho más recargado visualmente).
            const aplicarColor = () => {
                const { bgColor, textColor } = av_change_color_status(select.value)
                select.style.backgroundColor = bgColor;
                select.style.color = textColor;
                select.style.backgroundImage = AV_STATUS_CHEVRON_DARK;
            }
            aplicarColor()

            select.addEventListener('change', () => {

                const wrapper = select.closest('.js-list-cpt-sats__wrapper-select-status');
                if (!wrapper) return;
                const nodeSaveStatus = wrapper.querySelector('.js-list-cpt-sats__save-status');
                nodeSaveStatus.classList.add('is-active');

                aplicarColor()

            });
        })
    }

    const av_change_color_status = (estado) => {

        // Pastillas suaves (fondo claro + texto oscuro saturado), en línea con
        // el resto de la app (badges de prioridad, garantía, dashboard...).
        let bgColor = '#f1f5f9'; // sin seleccionar todavía
        let textColor = '#64748b';

        switch(estado) {
            case 'diagnosticar':
                bgColor = '#fef9c3'; textColor = '#854d0e';
                break;
            case 'cliente-espera':
                bgColor = '#ffedd5'; textColor = '#9a3412';
                break;
            case 'pieza':
                bgColor = '#f3e8ff'; textColor = '#6b21a8';
                break;
            case 'otro-sat':
                bgColor = '#ede9fe'; textColor = '#4c1d95';
                break;
            case 'reparar':
                bgColor = '#dbeafe'; textColor = '#1e3a8a';
                break;
            case 'reparado':
                bgColor = '#dcfce7'; textColor = '#166534';
                break;
            case 'no-reparado':
                bgColor = '#fee2e2'; textColor = '#991b1b';
                break;
            case 'garantia':
                bgColor = '#e2e8f0'; textColor = '#334155';
                break;
        }

        return {bgColor, textColor}
    }

   const av_save_status = () => {
        let timeoutId = null;

        // ── Banner de datos faltantes (reparación/piezas) ──
        // Antes también cubría precio/tipo de pago para "finalizar" desde el
        // listado; eso ya no existe (solo se finaliza desde el botón
        // "Finalizar SAT" del detalle), así que aquí solo queda el aviso de
        // reparación/piezas al marcar un SAT como "reparado".
        const banner        = document.querySelector('.js-list-cpt-sats__finalize-banner');
        const bannerTitle   = banner?.querySelector('.js-list-cpt-sats__finalize-banner-title');
        const bannerText    = banner?.querySelector('.js-list-cpt-sats__finalize-banner-text');
        const bannerError   = banner?.querySelector('.js-list-cpt-sats__finalize-banner-error');
        const bannerLinesField  = banner?.querySelector('.js-list-cpt-sats__finalize-banner-field-lines');
        const bannerLinesHidden = banner?.querySelector('.js-sat-lines-hidden');
        const bannerCancelBtn   = banner?.querySelector('.js-list-cpt-sats__finalize-banner-cancel');
        const bannerConfirmBtn  = banner?.querySelector('.js-list-cpt-sats__finalize-banner-confirm');

        // Líneas ya escritas en la tabla de "Artículos y servicios" del
        // banner (mismo formato que en el detalle del SAT, ver
        // components/c-sat-lines.php).
        const getBannerWidgetItems = (hidden) => {
            if (!hidden) return [];
            try {
                const items = JSON.parse(hidden.value);
                return Array.isArray(items) ? items.filter(it => (it.name_snapshot || '').trim()) : [];
            } catch (e) { return []; }
        };

        // Vacía la tabla del banner y avisa al widget para que se vuelva a
        // pintar (así no arrastra líneas del SAT anterior).
        const resetBannerRepairWidgets = () => {
            if (!bannerLinesHidden) return;
            bannerLinesHidden.value = '[]';
            bannerLinesHidden.dispatchEvent(new CustomEvent('av:repair-reset', { bubbles: false }));
        };

        let pendingContext = null;

        const closeBanner = () => {
            banner.classList.remove('is-active');
            if (bannerError) bannerError.textContent = '';
            resetBannerRepairWidgets();
            pendingContext = null;
        };

        const openBanner = (ctx) => {
            pendingContext = ctx;

            const accion = 'marcar el SAT como reparado';

            bannerTitle.textContent = 'Faltan datos para ' + accion;
            bannerText.textContent  = 'Antes de ' + accion + ' debes indicar los artículos o servicios.';
            if (bannerLinesField) bannerLinesField.style.display = '';
            bannerError.textContent = '';
            resetBannerRepairWidgets();

            banner.classList.add('is-active');
            banner.querySelector('.js-sat-lines-search')?.focus();
        };

        const sendStatusUpdate = (ctx, precioFinal, tipoPago, repairData) => {
            const { satId, statusValue, wrapper, select, nodeSaveStatus, nodePrice, saveBlock } = ctx;

            const formData = new FormData();
            formData.append('action', 'av_ajax_save_sat_status');
            formData.append('sat-id', satId);
            formData.append('status', statusValue);
            if (precioFinal !== null) formData.append('precio-final', precioFinal);
            if (tipoPago !== null) formData.append('tipo-pago', tipoPago);
            if (repairData) {
                formData.append('sat-lines-json', repairData.linesJson);
            }

            fetch(av_data.av_ajax_url, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(results => {
                const result = results.success

                if (!result) {
                    alert(typeof results.data === 'string' ? results.data : 'No se ha podido guardar el estado.');
                    window.location.reload();
                    return;
                }

                const price = results.data.price
                const payment = results.data.payment

                // El backend devuelve el precio tal cual se envió (con punto,
                // "75.00"): se reformatea con coma para que coincida con el
                // formato de toda la app (number_format($p, 2, ',', '.')).
                const fmtPriceEs = (p) => {
                    const n = parseFloat(String(p).replace(',', '.'))
                    return isNaN(n) ? p : n.toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
                }

                if(price !== null) nodePrice.textContent = price ? fmtPriceEs(price) + ' €' : '';
                if(payment) {
                    wrapper.dataset.payment = payment;
                    nodePrice.title = payment;
                }
                if(repairData) wrapper.dataset.repair = '1';
                wrapper.dataset.savedStatus = statusValue;

                saveBlock.classList.add('is-active')
                nodeSaveStatus.classList.add('no-click');
                select.classList.add('no-click')

                timeoutId = setTimeout(()=>{
                    saveBlock.classList.remove('is-active')
                    nodeSaveStatus.classList.remove('is-active');
                    nodeSaveStatus.classList.remove('no-click');
                    select.classList.remove('no-click')
                }, 3000)

            })
            .catch(error => {
                console.error('Error:', error);
            });
        };

        if (bannerCancelBtn) {
            bannerCancelBtn.addEventListener('click', () => {
                if (pendingContext) {
                    const { wrapper, select } = pendingContext;
                    select.value = wrapper.dataset.savedStatus || '';
                    select.dispatchEvent(new Event('change'));
                }
                closeBanner();
            });
        }

        if (bannerConfirmBtn) {
            bannerConfirmBtn.addEventListener('click', () => {
                if (!pendingContext) return;

                const linesItems = getBannerWidgetItems(bannerLinesHidden);
                if (linesItems.length === 0) {
                    bannerError.textContent = 'Añade algún artículo o servicio.';
                    return;
                }
                const repairData = {
                    linesJson: JSON.stringify(linesItems),
                };

                // Aquí no hay un campo "Precio" aparte que rellenar (a
                // diferencia del detalle del SAT): el precio final es la suma
                // de los importes CON IVA de cada línea (sale_price × qty ×
                // (1+iva/100)), mismo criterio que usa la tabla al mostrar el
                // subtotal. Así el listado (columna Total) queda al día en
                // cuanto se guarda desde aquí.
                let precioFinal = 0;
                linesItems.forEach(it => {
                    const net = parseFloat(it.sale_price) || 0;
                    const qty = parseFloat(it.quantity) || 0;
                    const vat = parseFloat(it.vat) || 0;
                    precioFinal += Math.round(net * qty * (1 + vat / 100) * 100) / 100;
                });

                const ctx = pendingContext;
                closeBanner();
                sendStatusUpdate(ctx, precioFinal > 0 ? precioFinal.toFixed(2) : '', null, repairData);
            });
        }

        // Vinculacion de los botones "guardar estado" de cada fila. Se
        // extrae a una funcion propia para poder volver a llamarla tras
        // sustituir las filas por el resultado del buscador asincrono
        // (las filas nuevas no tienen listeners hasta que se re-vinculan).
        const bindSaveButtons = () => {
            document.querySelectorAll('.js-list-cpt-sats__save-status').forEach(btn => {
                btn.addEventListener('click', event => {
                    event.preventDefault();

                    if (timeoutId) {
                        clearTimeout(timeoutId);
                    }

                    const saveBlock = document.querySelector('.b-save')
                    saveBlock.classList.remove('is-active')

                    const nodePrice = btn.closest('.c-list-cpt-sats__row').querySelector('.c-list-cpt-sats__price')

                    const wrapper = btn.closest('.js-list-cpt-sats__wrapper-select-status');
                    if (!wrapper) return;

                    const select = wrapper.querySelector('.js-list-cpt-sats__select-status');
                    const statusValue = select.value;
                    const satId = wrapper.dataset.satid;
                    const nodeSaveStatus = wrapper.querySelector('.js-list-cpt-sats__save-status');

                    const hasRepair  = wrapper.dataset.repair === '1';

                    // Igual que en el detalle del SAT: no se puede marcar como
                    // reparado sin indicar qué se ha reparado o qué piezas se
                    // han pedido (finalizar ya no se hace desde el listado,
                    // eso vive en el botón "Finalizar SAT" del detalle).
                    const needsRepair = statusValue === 'reparado' && !hasRepair;

                    const ctx = { satId, statusValue, wrapper, select, nodeSaveStatus, nodePrice, saveBlock };

                    if (needsRepair) {
                        openBanner(ctx);
                        return;
                    }

                    sendStatusUpdate(ctx, null, null, null);
                });
            });
        };

        bindSaveButtons();

        // Expuesta para que el buscador asincrono del listado de SATs pueda
        // re-vincular las filas nuevas sin duplicar los listeners del banner
        // (bannerCancelBtn/bannerConfirmBtn), que solo deben bindearse una vez.
        window.av_rebind_sats_save_status = bindSaveButtons;
    };

    const av_sat_client_picker = () => {

        const searchInput = document.querySelector('.js-sat-client-picker__search');
        const resultsBox   = document.querySelector('.js-sat-client-picker__results');

        if (!searchInput) return;

        const escapeHtml = (str) => (str || '').toString()
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');

        // Comparación sin tildes ni mayúsculas, manteniendo 1 carácter = 1 posición
        // (mismo criterio que av_highlight_matches en los listados).
        const fold = (str) => Array.from(str || '').map(ch => (ch.normalize('NFD')[0] || ch).toLowerCase()).join('');

        // Resalta la primera coincidencia de "term" dentro de "str", escapando el resto.
        const highlightMatch = (str, term) => {
            const raw = (str || '').toString();
            if (!term) return escapeHtml(raw);

            const chars    = Array.from(raw);
            const foldedStr  = fold(raw);
            const foldedTerm = fold(term);
            const idx = foldedTerm ? foldedStr.indexOf(foldedTerm) : -1;

            if (idx === -1) return escapeHtml(raw);

            const before = chars.slice(0, idx).join('');
            const match  = chars.slice(idx, idx + foldedTerm.length).join('');
            const after  = chars.slice(idx + foldedTerm.length).join('');

            return escapeHtml(before) + '<mark class="c-filters-mark">' + escapeHtml(match) + '</mark>' + escapeHtml(after);
        };

        let debounceId = null;

        searchInput.addEventListener('input', () => {
            const term = searchInput.value.trim();

            if (debounceId) clearTimeout(debounceId);

            if (term.length < 2) {
                resultsBox.innerHTML = '';
                return;
            }

            debounceId = setTimeout(() => {
                const formData = new FormData();
                formData.append('action', 'av_ajax_search_clients_picker');
                formData.append('term', term);

                fetch(av_data.av_ajax_url, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(results => {
                    const clients = (results && results.data && results.data.clients) || [];

                    if (!clients.length) {
                        resultsBox.innerHTML = '<div class="c-sat-client-picker__no-results">No se ha encontrado ningún cliente.</div>';
                        return;
                    }

                    resultsBox.innerHTML = clients.map(client => `
                        <a class="c-sat-client-picker__result" href="${escapeHtml(client.url)}">
                            <span class="c-sat-client-picker__result-name">${highlightMatch(client.name, term)}</span>
                            <span class="c-sat-client-picker__result-meta">${highlightMatch(client.dni, term)}${client.phone ? ' · ' + highlightMatch(client.phone, term) : ''}</span>
                        </a>
                    `).join('');
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            }, 300);
        });
    };

    const av_check_user = () => {

        const node = document.querySelectorAll('.js-check-user')
        const nodeMessage = document.querySelector('.c-client-form__wrapper-message')
        const userName = document.querySelector('.b-user-details__name')
        const userDni = document.querySelector('.b-user-details__dni')
        const userTel = document.querySelector('.b-user-details__tel')
        const buttonForm = document.querySelector('.c-client-form__save')
        const nameNode = document.querySelector('.c-client-form__input-name')
        const phoneNode = document.querySelector('.c-client-form__input-phone')
        const nodePrincipal = document.querySelector('.c-client-form')
        
        node.forEach(item =>{            
            
            item.addEventListener('input', () => {
                const getPhoneExt = document.querySelector('.c-client-form__input-phone-extension').value
                const getPhone = document.querySelector('.c-client-form__input-phone').value
                let numChar = item.value.length
                if(numChar >= 2){
    
                    const formData = new FormData();
                    formData.append('action', 'av_ajax_check_user');
                    formData.append('value', item.value);  
                    formData.append('type',  item.dataset.id);  
                    formData.append('extension',  getPhoneExt);  
                    formData.append('phone',  getPhone);                                                
    
                    fetch(av_data.av_ajax_url, {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(results => {
                        const result = results.data.result
                        const userDetails = results.data.client 
                        const detailUrl = userDetails.detail 
                        const createSatUrl = userDetails.createSatUrl                        

                        item.classList.remove('no-existe') 
                        item.classList.remove('existe')                         
                        nodeMessage.classList.remove('is-active')                                 
                        if (result) {                            
                            item.classList.add('no-existe')
                            userName.innerHTML = ''
                            userDni.innerHTML = ''
                            userTel.innerHTML = '' 
                            if(nameNode.classList.contains('no-existe') && phoneNode.classList.contains('no-existe') || nodePrincipal.classList.contains('modificar')){
                                buttonForm.removeAttribute('disabled')
                                buttonForm.classList.remove('is-disabled')   
                            }                         
                        }else{
                            item.classList.add('existe')
                            nodeMessage.classList.add('is-active')
                            document.querySelector('.b-user-details__detail-link').href = detailUrl
                            document.querySelector('.b-user-details__create-sat-link').href = createSatUrl
                            userName.innerHTML = userDetails.name
                            userDni.innerHTML = userDetails.dni
                            userTel.innerHTML = '+' + userDetails.telExt + ' ' + userDetails.tel                         
                        }
    
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
                }
            })
        })


    }

    const av_user_details = () => {

        const node = document.querySelectorAll('.js-user-details')
        const userDetails = document.querySelector('.b-user-details')
        node.forEach(item => {
            item.addEventListener('click', () => {               
                if(item.classList.contains('for-close')){
                    userDetails.classList.remove('is-active')
                }
                if(item.classList.contains('for-open')){
                    userDetails.classList.add('is-active')
                }
            })
        })

    }

    const av_search_select = () => {

        const nodeSelect = document.querySelector('.js-search-select')
        const nodeInputsWrapper = document.querySelector('.c-list-cpt-sats__wrapper-inputs-search')
        const nodeButton = document.querySelector('.c-list-cpt-sats__search-button')
            
        nodeSelect.addEventListener('change', () => {
            const selectedValue = nodeSelect.value         
            nodeButton.removeAttribute('hidden')
            nodeButton.setAttribute('name', selectedValue)
            if(selectedValue==='selecciona') {
                nodeButton.setAttribute('hidden', 'hidden')
            }

            const selectedInput = nodeInputsWrapper.querySelector(`.c-list-cpt-sats__search[data-id="${selectedValue}"]`)
            nodeInputsWrapper.querySelectorAll('.c-list-cpt-sats__search').forEach(e => {
                e.setAttribute('hidden', 'hidden')
            })
            selectedInput.removeAttribute('hidden')
        })   

    }

    const av_check_form_changed = () => {

        const formulario = document.querySelector('.c-sat-form__form');
        if (!formulario) return;
        let cambios = false;

        const saveBtn       = formulario.querySelector('.js-sat-form__save-btn');
        const cancelBtn     = formulario.querySelector('.js-sat-form__cancel-btn');
        const saveBtnLocked = saveBtn && saveBtn.dataset.locked === '1';

        // Capturar estado inicial de todos los campos del formulario
        const snap = new Map();
        Array.from(formulario.elements).forEach(el => {
            snap.set(el, {
                value:   el.value,
                checked: (el.type === 'checkbox' || el.type === 'radio') ? el.checked : undefined,
            });
        });

        const isFieldDirty = (el) => {
            const original = snap.get(el);
            if (!original) return false;
            if (el.type === 'checkbox' || el.type === 'radio') return el.checked !== original.checked;
            return el.value !== original.value;
        };

        // El campo visible a marcar: en Reparación/Piezas el que cambia de
        // verdad es un input oculto (JSON), así que se marca el widget entero.
        const dirtyTarget = (el) => el.closest('.js-repair-widget')
            || el.closest('.c-sat-form__wrapper-input')
            || el;

        // Compara contra el valor guardado al cargar la página: si el campo
        // vuelve a coincidir (p.ej. lo escribes y luego lo borras) se
        // desmarca, así el marcado siempre refleja lo que de verdad falta
        // por guardar, no solo "lo que se tocó en algún momento".
        const updateFieldMark = (el) => {
            dirtyTarget(el).classList.toggle('is-unsaved', isFieldDirty(el));
        };

        const clearAllMarks = () => {
            formulario.querySelectorAll('.is-unsaved').forEach(el => el.classList.remove('is-unsaved'));
        };

        // El marcado NO se hace al instante mientras se escribe (sería
        // demasiado ruidoso): solo se calcula justo cuando se intenta salir
        // sin guardar, dentro de "beforeunload" más abajo.
        const setDirty = () => {
            if (formulario._avRestoring) return;
            cambios = true;
            if (saveBtn && !saveBtnLocked) {
                saveBtn.disabled = false;
                saveBtn.title = '';
            }
            cancelBtn?.classList.remove('is-hidden');
        };

        const cancelChanges = () => {
            formulario._avRestoring = true;
            snap.forEach(({ value, checked }, el) => {
                if (el.type === 'checkbox' || el.type === 'radio') {
                    el.checked = checked;
                } else {
                    el.value = value;
                }
                // Re-renderizar widgets de reparación/piezas
                if (el.classList.contains('js-repair-hidden')) {
                    el.dispatchEvent(new CustomEvent('av:repair-reset', { bubbles: false }));
                }
                // Desmarcar las fotos que estaban marcadas para eliminar
                if (el.classList.contains('js-photo-remove-input')) {
                    el.dispatchEvent(new CustomEvent('av:photos-reset', { bubbles: false }));
                }
            });
            // Actualizar estado del botón de factura según precio restaurado
            const priceInput = formulario.querySelector('[name="price"]');
            if (priceInput) priceInput.dispatchEvent(new Event('change', { bubbles: true }));
            formulario._avRestoring = false;
            cambios = false;
            clearAllMarks();
            if (saveBtn) {
                saveBtn.disabled = true;
                saveBtn.title = 'Modifica algún campo para poder guardar';
            }
            cancelBtn?.classList.add('is-hidden');
        };

        formulario.addEventListener('input', setDirty);

        cancelBtn?.addEventListener('click', cancelChanges);

        window.addEventListener('beforeunload', (e) => {
            if (!cambios) return;

            // Justo antes de mostrar el aviso nativo del navegador se marcan
            // los campos que de verdad tienen cambios sin guardar.
            Array.from(formulario.elements).forEach(updateFieldMark);

            e.preventDefault();
            e.returnValue = '';

            // "beforeunload" no avisa si el usuario decide quedarse o salir.
            // Si sigue aquí un momento después es que ha cancelado: le
            // llevamos el scroll hasta el primer campo marcado.
            setTimeout(() => {
                const primerMarcado = formulario.querySelector('.is-unsaved');
                primerMarcado?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }, 200);
        });

        formulario.addEventListener('submit', () => {
            cambios = false;
            clearAllMarks();
            cancelBtn?.classList.add('is-hidden');
        });
    }

    const av_repair_list = (widget) => {
        if (!widget) return;

        const addBtn   = widget.querySelector('.js-repair-add');
        const input    = widget.querySelector('.js-repair-input');
        const priceInp = widget.querySelector('.js-repair-price');
        const list     = widget.querySelector('.js-repair-list');
        const hidden   = widget.querySelector('.js-repair-hidden');
        if (!addBtn || !input || !list || !hidden) return;

        const form       = widget.closest('.c-sat-form__form') || widget.closest('form');
        const priceField = form ? form.querySelector('[name="price"]') : null;

        const recalcTotal = () => {
            if (!priceField || !form) return;
            let total = 0;
            form.querySelectorAll('.js-repair-widget').forEach(w => {
                const h = w.querySelector('.js-repair-hidden');
                if (!h) return;
                try {
                    const its = JSON.parse(h.value);
                    if (Array.isArray(its)) its.forEach(it => {
                        const p = parseFloat(it.price);
                        if (!isNaN(p)) total += p;
                    });
                } catch (ex) {}
            });
            priceField.value = total > 0 ? total.toFixed(2) : '';
            priceField.dispatchEvent(new Event('input', { bubbles: true }));
        };

        const getItems = () => {
            const val = hidden.value.trim();
            if (!val) return [];
            try {
                const parsed = JSON.parse(val);
                if (Array.isArray(parsed)) return parsed;
            } catch (e) {}
            return val.split('\n').filter(l => l.trim()).map(text => ({ text: text.trim(), price: '' }));
        };

        const syncHidden = (items) => {
            hidden.value = JSON.stringify(items);
            hidden.dispatchEvent(new Event('input',  { bubbles: true }));
            hidden.dispatchEvent(new Event('change', { bubbles: true }));
            recalcTotal();
        };

        const fmtPrice = (p) => {
            const n = parseFloat(p);
            return isNaN(n) ? p : n.toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        };

        const EDIT_ICON = `<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>`;

        const renderItems = (items) => {
            list.innerHTML = '';
            items.forEach((item, idx) => {
                if (!item.text.trim()) return;
                const li = document.createElement('li');
                li.className = 'c-sat-form__repair-item';
                const priceHtml = item.price
                    ? `<span class="c-sat-form__repair-item-price">${fmtPrice(item.price)} €</span>`
                    : '';
                li.innerHTML = `<span class="c-sat-form__repair-item-text">${item.text.replace(/</g, '&lt;')}</span>${priceHtml}<button type="button" class="c-sat-form__repair-edit js-repair-edit" data-idx="${idx}" aria-label="Editar">${EDIT_ICON}</button><button type="button" class="c-sat-form__repair-remove js-repair-remove" data-idx="${idx}" aria-label="Eliminar">×</button>`;
                list.appendChild(li);
            });
        };

        const enterEditMode = (li, idx) => {
            const items = getItems();
            const item  = items[idx];
            li.classList.add('c-sat-form__repair-item--editing');
            li.innerHTML = `<input type="text" class="c-sat-form__input c-sat-form__repair-edit-text" value="${item.text.replace(/"/g, '&quot;').replace(/</g, '&lt;')}"><input type="number" step="any" class="c-sat-form__input c-sat-form__repair-price-input c-sat-form__repair-edit-price" value="${item.price || ''}" placeholder="Precio €"><button type="button" class="c-sat-form__repair-save js-repair-save" aria-label="Guardar">✓</button><button type="button" class="c-sat-form__repair-cancel js-repair-cancel" aria-label="Cancelar">✕</button>`;
            const textIn  = li.querySelector('.c-sat-form__repair-edit-text');
            const priceIn = li.querySelector('.c-sat-form__repair-edit-price');
            textIn.focus();
            const save = () => {
                const newText = textIn.value.trim();
                if (!newText) return;
                items[idx] = { text: newText, price: priceIn.value.trim() };
                syncHidden(items);
                renderItems(items);
            };
            li.querySelector('.js-repair-save').addEventListener('click', save);
            li.querySelector('.js-repair-cancel').addEventListener('click', () => renderItems(items));
            [textIn, priceIn].forEach(el => {
                el.addEventListener('keydown', (ev) => {
                    if (ev.key === 'Enter')  { ev.preventDefault(); save(); }
                    if (ev.key === 'Escape') { renderItems(items); }
                });
            });
        };

        renderItems(getItems());

        // Restaurar lista desde el botón Cancelar del formulario
        hidden.addEventListener('av:repair-reset', () => {
            renderItems(getItems());
            recalcTotal();
        });

        const addItem = () => {
            const val = input.value.trim();
            if (!val) return;
            const price = priceInp ? priceInp.value.trim() : '';
            const items = getItems();
            items.push({ text: val, price });
            syncHidden(items);
            renderItems(items);
            input.value = '';
            if (priceInp) priceInp.value = '';
            input.focus();
        };

        addBtn.addEventListener('click', addItem);

        // Enter en cualquiera de los dos campos de alta (texto o precio)
        // añade el ítem, igual que pulsar "Añadir" — nunca debe enviar el
        // formulario completo del SAT.
        [input, priceInp].forEach(el => {
            el?.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') { e.preventDefault(); addItem(); }
            });
        });

        list.addEventListener('click', (e) => {
            const removeBtn = e.target.closest('.js-repair-remove');
            if (removeBtn) {
                const idx = parseInt(removeBtn.dataset.idx, 10);
                const items = getItems();
                items.splice(idx, 1);
                syncHidden(items);
                renderItems(items);
                return;
            }
            const editBtn = e.target.closest('.js-repair-edit');
            if (editBtn) {
                enterEditMode(editBtn.closest('li'), parseInt(editBtn.dataset.idx, 10));
            }
        });
    };

    // ── Autocomplete de Servicios guardados dentro de "Reparación" ──────────
    // Capa aparte sobre el mismo widget de av_repair_list() (mismo input de
    // texto, mismo campo de precio, mismo botón "Añadir"): no lo sustituye,
    // solo le añade atajos. Escribir texto libre y pulsar "Añadir" sigue
    // funcionando exactamente igual que antes (eso lo sigue gestionando
    // av_repair_list); esto solo rellena texto+precio desde un servicio ya
    // guardado, o desde uno nuevo creado al vuelo, y reutiliza el mismo botón
    // "Añadir" para no duplicar la lógica de alta de línea.
    const av_repair_servicios_autocomplete = (widget) => {
        if (!widget) return;

        const input      = widget.querySelector('.js-repair-servicios-search');
        const priceInp   = widget.querySelector('.js-repair-price');
        const addBtn     = widget.querySelector('.js-repair-add');
        const resultsBox = widget.querySelector('.js-repair-servicios-results');
        if (!input || !addBtn || !resultsBox) return;

        const escapeHtml = (str) => (str || '').toString()
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        const fmtEUR = (n) => (parseFloat(n) || 0).toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        // Añade "- descripción" al texto de la línea si el servicio tiene una
        // guardada (ver av_ajax_search_servicios_picker()).
        const withDescription = (name, description) => {
            const d = (description || '').trim();
            return d ? `${name} - ${d}` : name;
        };

        const closeResults = () => {
            resultsBox.innerHTML = '';
            resultsBox.classList.add('is-hidden');
        };

        // Dentro del banner "Finalizar SAT" del listado (que tiene su propio
        // scroll, ver c-sat-form.scss) el desplegable pasa a position:fixed
        // para escapar de ese overflow — su posición no la resuelve el CSS
        // solo ahí, hay que calcularla en JS cada vez que se muestra, justo
        // encima del input. Fuera del banner (SAT-detail) se deja tal cual
        // (position:absolute; top:100% ya puesto por CSS), sin tocar nada.
        const inFinalizeBanner = !!widget.closest('.c-list-cpt-sats__finalize-banner');
        const showResults = () => {
            if (inFinalizeBanner) {
                const rect = input.getBoundingClientRect();
                resultsBox.style.left = `${rect.left}px`;
                resultsBox.style.width = `${rect.width}px`;
                resultsBox.style.bottom = `${window.innerHeight - rect.top + 6}px`;
            }
            resultsBox.classList.remove('is-hidden');
        };

        // Rellena texto+precio y reutiliza el botón "Añadir" ya existente
        // (av_repair_list) en vez de reimplementar el alta de línea aquí.
        const pickResult = (name, price) => {
            input.value = name;
            if (priceInp) priceInp.value = price || '';
            closeResults();
            addBtn.click();
        };

        let debounceId = null;
        let lastResults = [];

        const renderResults = (term) => {
            let html = lastResults.map((s, idx) => `
                <button type="button" class="c-sat-client-picker__result js-repair-servicio-result" data-idx="${idx}">
                    <span class="c-sat-client-picker__result-name">${escapeHtml(s.name)}</span>
                    <span class="c-sat-client-picker__result-meta">${fmtEUR(s.price)} €</span>
                </button>
            `).join('');

            if (!lastResults.length) {
                html += `<div class="c-sat-client-picker__no-results">No se ha encontrado ningún servicio guardado.</div>`;
            }

            // Solo administrador (av_data.can_create_servicios, ver
            // func/enqueues.php): un técnico sin ese permiso solo ve los
            // resultados/el aviso de arriba y sigue pudiendo añadir texto
            // libre con el botón "Añadir" de siempre.
            if (av_data.can_create_servicios) {
                html += `
                    <button type="button" class="c-sat-client-picker__result js-repair-servicio-create">
                        <span class="c-sat-client-picker__result-name">+ Crear servicio "${escapeHtml(term)}"</span>
                    </button>
                `;
            }

            resultsBox.innerHTML = html;
            showResults();
        };

        input.addEventListener('input', () => {
            const term = input.value.trim();
            if (debounceId) clearTimeout(debounceId);

            if (term.length < 2) {
                closeResults();
                return;
            }

            debounceId = setTimeout(() => {
                const formData = new FormData();
                formData.append('action', 'av_ajax_search_servicios_picker');
                formData.append('nonce', av_data.nonce_servicios_picker);
                formData.append('term', term);

                fetch(av_data.av_ajax_url, { method: 'POST', body: formData })
                    .then(res => res.json())
                    .then(json => {
                        lastResults = (json && json.data) || [];
                        renderResults(term);
                    })
                    .catch(() => {
                        resultsBox.innerHTML = '<div class="c-sat-client-picker__no-results">Error de conexión. Inténtalo de nuevo.</div>';
                        showResults();
                    });
            }, 300);
        });

        resultsBox.addEventListener('click', (e) => {
            const resultBtn = e.target.closest('.js-repair-servicio-result');
            if (resultBtn) {
                const s = lastResults[parseInt(resultBtn.dataset.idx, 10)];
                if (s) pickResult(withDescription(s.name, s.description), s.price);
                return;
            }

            const createBtn = e.target.closest('.js-repair-servicio-create');
            if (createBtn) {
                const titulo = input.value.trim();
                if (!titulo) return;
                createBtn.disabled = true;
                createBtn.textContent = 'Creando...';

                const formData = new FormData();
                formData.append('action', 'av_ajax_quick_create_servicio');
                formData.append('nonce', av_data.nonce_servicios_picker);
                formData.append('titulo', titulo);
                formData.append('precio', priceInp ? priceInp.value : '');

                fetch(av_data.av_ajax_url, { method: 'POST', body: formData })
                    .then(res => res.json())
                    .then(json => {
                        if (json && json.success) {
                            pickResult(json.data.name, json.data.price);
                        } else {
                            alert((json && json.data) || 'No se ha podido crear el servicio.');
                        }
                    })
                    .catch(() => alert('Error de conexión. Inténtalo de nuevo.'));
            }
        });

        // Cierra el desplegable también al añadir texto libre con "Añadir"
        // (no solo al elegir una sugerencia, que ya cierra por su cuenta en
        // pickResult): si no, la última búsqueda se queda visible aunque la
        // línea añadida no tenga nada que ver con ella.
        addBtn.addEventListener('click', closeResults);

        document.addEventListener('click', (e) => {
            if (!widget.contains(e.target)) closeResults();
        });
    };

    // ── Autocomplete de Artículos guardados dentro de "Artículos" (antes
    // "Piezas pedidas") ──────────────────────────────────────────────────────
    // Mismo patrón que av_repair_servicios_autocomplete(): capa aparte sobre
    // el mismo widget de av_repair_list (texto libre + precio + "Añadir"
    // siguen funcionando igual). A diferencia de Servicios, aquí NO hay
    // "+ Crear artículo": dar de alta un artículo real exige categoría (de
    // ahí sale el SKU, ver inc/articulos/sku.php) y no tiene sentido
    // improvisarlo desde aquí — si no existe, se escribe como texto libre y
    // ya, igual que siempre.
    const av_repair_articulos_autocomplete = (widget) => {
        if (!widget) return;

        const input      = widget.querySelector('.js-repair-articulos-search');
        const priceInp   = widget.querySelector('.js-repair-price');
        const addBtn     = widget.querySelector('.js-repair-add');
        const resultsBox = widget.querySelector('.js-repair-articulos-results');
        if (!input || !addBtn || !resultsBox) return;

        const escapeHtml = (str) => (str || '').toString()
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        const fmtEUR = (n) => (parseFloat(n) || 0).toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        // Añade "- descripción" al texto de la línea si el artículo tiene una
        // guardada (ver av_ajax_search_articulos_picker()).
        const withDescription = (name, description) => {
            const d = (description || '').trim();
            return d ? `${name} - ${d}` : name;
        };

        const closeResults = () => {
            resultsBox.innerHTML = '';
            resultsBox.classList.add('is-hidden');
        };

        // Dentro del banner "Finalizar SAT" del listado (que tiene su propio
        // scroll, ver c-sat-form.scss) el desplegable pasa a position:fixed
        // para escapar de ese overflow — su posición no la resuelve el CSS
        // solo ahí, hay que calcularla en JS cada vez que se muestra, justo
        // encima del input. Fuera del banner (SAT-detail) se deja tal cual
        // (position:absolute; top:100% ya puesto por CSS), sin tocar nada.
        const inFinalizeBanner = !!widget.closest('.c-list-cpt-sats__finalize-banner');
        const showResults = () => {
            if (inFinalizeBanner) {
                const rect = input.getBoundingClientRect();
                resultsBox.style.left = `${rect.left}px`;
                resultsBox.style.width = `${rect.width}px`;
                resultsBox.style.bottom = `${window.innerHeight - rect.top + 6}px`;
            }
            resultsBox.classList.remove('is-hidden');
        };

        // Rellena texto+precio y reutiliza el botón "Añadir" ya existente
        // (av_repair_list) en vez de reimplementar el alta de línea aquí.
        const pickResult = (name, price) => {
            input.value = name;
            if (priceInp) priceInp.value = price || '';
            closeResults();
            addBtn.click();
        };

        let debounceId = null;
        let lastResults = [];

        const renderResults = () => {
            let html = lastResults.map((art, idx) => {
                // "SKU · Categoría · Precio" (categoría solo si el artículo
                // tiene una asignada — ver av_ajax_search_articulos_picker(),
                // inc/articulos/sat-integration.php).
                const metaParts = [art.sku, art.category, fmtEUR(art.unit_price) + ' €'].filter(Boolean);
                return `
                <button type="button" class="c-sat-client-picker__result js-repair-articulo-result" data-idx="${idx}">
                    <span class="c-sat-client-picker__result-name">${escapeHtml(art.name)}</span>
                    <span class="c-sat-client-picker__result-meta">${escapeHtml(metaParts.join(' · '))}</span>
                </button>
            `;
            }).join('');

            if (!lastResults.length) {
                html += `<div class="c-sat-client-picker__no-results">No se ha encontrado ningún artículo.</div>`;
            }

            resultsBox.innerHTML = html;
            showResults();
        };

        input.addEventListener('input', () => {
            const term = input.value.trim();
            if (debounceId) clearTimeout(debounceId);

            if (term.length < 2) {
                closeResults();
                return;
            }

            debounceId = setTimeout(() => {
                const formData = new FormData();
                formData.append('action', 'av_ajax_search_articulos_picker');
                formData.append('nonce', av_data.nonce_articulos_picker);
                formData.append('term', term);

                fetch(av_data.av_ajax_url, { method: 'POST', body: formData })
                    .then(res => res.json())
                    .then(json => {
                        lastResults = (json && json.data) || [];
                        renderResults();
                    })
                    .catch(() => {
                        resultsBox.innerHTML = '<div class="c-sat-client-picker__no-results">Error de conexión. Inténtalo de nuevo.</div>';
                        showResults();
                    });
            }, 300);
        });

        resultsBox.addEventListener('click', (e) => {
            const resultBtn = e.target.closest('.js-repair-articulo-result');
            if (!resultBtn) return;
            const art = lastResults[parseInt(resultBtn.dataset.idx, 10)];
            if (!art) return;
            // La categoría se lleva a la línea añadida (no solo se ve en el
            // desplegable): una vez añadido, "iphone 11" solo no dice si es
            // una pantalla, una batería, etc.
            let text = art.category ? `${art.name} (${art.category})` : art.name;
            text = withDescription(text, art.description);
            pickResult(text, art.unit_price);
        });

        // Cierra el desplegable también al añadir texto libre con "Añadir".
        addBtn.addEventListener('click', closeResults);

        document.addEventListener('click', (e) => {
            if (!widget.contains(e.target)) closeResults();
        });
    };

    // ── Artículos utilizados en el SAT (buscador + líneas con snapshot) ─────
    // A diferencia de Reparación/Artículos (texto libre), aquí las líneas
    // vienen de un buscador contra el catálogo real (inc/articulos/) y llevan
    // snapshot de nombre/SKU/precio: si el artículo cambia después, un SAT
    // antiguo conserva los datos tal como eran cuando se usó.
    const av_sat_articulos_widget = (widget) => {
        if (!widget) return;

        const searchInput = widget.querySelector('.js-sat-articulos-search');
        const qtyInput     = widget.querySelector('.js-sat-articulos-qty');
        const resultsBox   = widget.querySelector('.js-sat-articulos-results');
        const list         = widget.querySelector('.js-sat-articulos-list');
        const hidden       = widget.querySelector('.js-sat-articulos-hidden');
        if (!searchInput || !list || !hidden) return;

        const escapeHtml = (str) => (str || '').toString()
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');

        const fmtNum = (n, decimals) => {
            const v = parseFloat(n);
            return isNaN(v) ? '0' : v.toLocaleString('es-ES', { minimumFractionDigits: decimals, maximumFractionDigits: decimals });
        };

        const getItems = () => {
            try {
                const parsed = JSON.parse(hidden.value);
                return Array.isArray(parsed) ? parsed : [];
            } catch (e) { return []; }
        };

        const syncHidden = (items) => {
            hidden.value = JSON.stringify(items);
            hidden.dispatchEvent(new Event('input', { bubbles: true }));
            hidden.dispatchEvent(new Event('change', { bubbles: true }));
        };

        const renderItems = (items) => {
            list.innerHTML = '';
            items.forEach((item, idx) => {
                const li = document.createElement('li');
                li.className = 'c-sat-form__repair-item';
                li.innerHTML = `<span class="c-sat-form__repair-item-text">${escapeHtml(item.name_snapshot)} <small>(${escapeHtml(item.sku_snapshot)})</small> × ${fmtNum(item.quantity, 0)}</span><span class="c-sat-form__repair-item-price">${fmtNum(item.subtotal, 2)} €</span><button type="button" class="c-sat-form__repair-remove js-sat-articulos-remove" data-idx="${idx}" aria-label="Eliminar">×</button>`;
                list.appendChild(li);
            });
        };

        renderItems(getItems());

        list.addEventListener('click', (e) => {
            const btn = e.target.closest('.js-sat-articulos-remove');
            if (!btn) return;
            const items = getItems();
            items.splice(parseInt(btn.dataset.idx, 10), 1);
            syncHidden(items);
            renderItems(items);
        });

        const addArticulo = (art) => {
            const qty = parseFloat((qtyInput?.value || '1').replace(',', '.')) || 1;

            if (!art.allow_negative_stock && qty > art.stock) {
                resultsBox.innerHTML = `<div class="c-sat-client-picker__no-results">Stock insuficiente. Stock disponible: ${fmtNum(art.stock, 0)}</div>`;
                return;
            }

            const items = getItems();
            const existing = items.find(it => it.article_id === art.id);
            if (existing) {
                existing.quantity += qty;
                existing.subtotal = Math.round(existing.quantity * existing.unit_price * 100) / 100;
            } else {
                items.push({
                    article_id: art.id,
                    name_snapshot: art.name,
                    sku_snapshot: art.sku,
                    quantity: qty,
                    unit_price: art.unit_price,
                    vat: art.vat,
                    subtotal: Math.round(qty * art.unit_price * 100) / 100,
                });
            }

            syncHidden(items);
            renderItems(items);
            searchInput.value = '';
            if (qtyInput) qtyInput.value = '1';
            resultsBox.innerHTML = '';
            resultsBox.classList.add('is-hidden');
        };

        let debounceId = null;
        let lastResults = [];

        searchInput.addEventListener('input', () => {
            const term = searchInput.value.trim();
            if (debounceId) clearTimeout(debounceId);

            if (term.length < 2) {
                resultsBox.innerHTML = '';
                resultsBox.classList.add('is-hidden');
                return;
            }

            debounceId = setTimeout(() => {
                const formData = new FormData();
                formData.append('action', 'av_ajax_search_articulos_picker');
                formData.append('nonce', av_data.nonce_articulos_picker);
                formData.append('term', term);

                fetch(av_data.av_ajax_url, { method: 'POST', body: formData })
                    .then(response => response.json())
                    .then(results => {
                        lastResults = (results && results.data) || [];
                        resultsBox.classList.remove('is-hidden');

                        if (!lastResults.length) {
                            resultsBox.innerHTML = '<div class="c-sat-client-picker__no-results">No se ha encontrado ningún artículo.</div>';
                            return;
                        }

                        resultsBox.innerHTML = lastResults.map((art, idx) => `
                            <button type="button" class="c-sat-client-picker__result js-sat-articulos-result" data-idx="${idx}">
                                <span class="c-sat-client-picker__result-name">${escapeHtml(art.name)}</span>
                                <span class="c-sat-client-picker__result-meta">${escapeHtml(art.sku)} · Stock: ${fmtNum(art.stock, 0)} · ${fmtNum(art.unit_price, 2)} €</span>
                            </button>
                        `).join('');
                    })
                    .catch(() => {
                        resultsBox.innerHTML = '<div class="c-sat-client-picker__no-results">Error de conexión. Inténtalo de nuevo.</div>';
                    });
            }, 300);
        });

        resultsBox?.addEventListener('click', (e) => {
            const btn = e.target.closest('.js-sat-articulos-result');
            if (!btn) return;
            const art = lastResults[parseInt(btn.dataset.idx, 10)];
            if (art) addArticulo(art);
        });
    };

    // ── Pedidos: buscador de cliente (en página, sin navegar) ────────────
    // A diferencia de av_sat_client_picker() (que navega a otra página al
    // elegir), aquí hace falta seleccionar SIN salir del formulario de
    // pedido: los resultados se pintan como botones (como en
    // av_sat_articulos_widget) y al pulsar uno se rellena un campo oculto.
    // Reutiliza tal cual av_ajax_search_clients_picker (mismo endpoint que ya
    // usa "Crear SAT").
    const av_pedido_client_picker = () => {

        const form = document.querySelector('.js-pedido-form');
        if (!form) return;

        const hiddenId     = form.querySelector('.js-pedido-client-id');
        const selectedBox  = form.querySelector('.js-pedido-client-selected');
        const nameEl       = form.querySelector('.js-pedido-client-name');
        const metaEl       = form.querySelector('.js-pedido-client-meta');
        const changeBtn    = form.querySelector('.js-pedido-client-change');
        const searchWrap   = form.querySelector('.js-pedido-client-search-wrap');
        const searchInput  = form.querySelector('.js-pedido-client-search');
        const resultsBox   = form.querySelector('.js-pedido-client-results');
        const summaryClient = form.querySelector('.js-pedido-summary-client');
        const manualToggle  = form.querySelector('.js-pedido-client-manual-toggle');
        const manualBox     = form.querySelector('.js-pedido-client-manual');
        const manualName    = form.querySelector('.js-pedido-manual-name');

        if (!hiddenId || !searchInput) return;

        const escapeHtml = (str) => (str || '').toString()
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');

        const selectClient = (id, name, phone) => {
            hiddenId.value = id;
            // No es un input que el usuario teclee: sin esto, elegir cliente
            // no dispara ningún evento y av_pedido_dirty_save_button() no se
            // entera de que el formulario ha cambiado.
            hiddenId.dispatchEvent(new Event('change', { bubbles: true }));
            if (nameEl) nameEl.textContent = name || '';
            if (metaEl) metaEl.textContent = phone || '';
            if (summaryClient) summaryClient.textContent = name || '';
            selectedBox?.classList.remove('is-hidden');
            searchWrap?.classList.add('is-hidden');
            if (resultsBox) { resultsBox.innerHTML = ''; resultsBox.classList.add('is-hidden'); }
            searchInput.value = '';
            // Un cliente de la BDD sustituye al escrito a mano.
            manualBox?.classList.add('is-hidden');
            manualBox?.querySelectorAll('input').forEach(inp => {
                if (inp.type === 'checkbox') inp.checked = false;
                else if (inp.name === 'manual-client-name' || inp.name === 'manual-client-phone') inp.value = '';
            });
        };

        // ── Cliente escrito a mano (sin ficha en la BDD) ───────────────────
        manualToggle?.addEventListener('click', () => {
            const opening = manualBox?.classList.contains('is-hidden');
            manualBox?.classList.toggle('is-hidden', !opening);
            manualToggle.textContent = opening ? 'Buscar cliente existente' : 'Escribir cliente a mano';
            if (opening) manualName?.focus();
        });

        manualName?.addEventListener('input', () => {
            if (summaryClient && (!hiddenId.value || hiddenId.value === '0')) summaryClient.textContent = manualName.value.trim() || 'Sin elegir';
        });

        changeBtn?.addEventListener('click', () => {
            searchWrap?.classList.remove('is-hidden');
            searchInput.focus();
        });

        let debounceId = null;
        let lastResults = [];

        searchInput.addEventListener('input', () => {
            const term = searchInput.value.trim();
            if (debounceId) clearTimeout(debounceId);

            if (term.length < 2) {
                resultsBox.innerHTML = '';
                resultsBox.classList.add('is-hidden');
                return;
            }

            debounceId = setTimeout(() => {
                const formData = new FormData();
                formData.append('action', 'av_ajax_search_clients_picker');
                formData.append('term', term);

                fetch(av_data.av_ajax_url, { method: 'POST', body: formData })
                    .then(response => response.json())
                    .then(results => {
                        lastResults = (results && results.data && results.data.clients) || [];
                        resultsBox.classList.remove('is-hidden');

                        if (!lastResults.length) {
                            resultsBox.innerHTML = '<div class="c-sat-client-picker__no-results">No se ha encontrado ningún cliente.</div>';
                            return;
                        }

                        resultsBox.innerHTML = lastResults.map((c, idx) => `
                            <button type="button" class="c-sat-client-picker__result js-pedido-client-result" data-idx="${idx}">
                                <span class="c-sat-client-picker__result-name">${escapeHtml(c.name)}</span>
                                <span class="c-sat-client-picker__result-meta">${escapeHtml(c.dni)}${c.phone ? ' · ' + escapeHtml(c.phone) : ''}</span>
                            </button>
                        `).join('');
                    })
                    .catch(() => {
                        resultsBox.innerHTML = '<div class="c-sat-client-picker__no-results">Error de conexión. Inténtalo de nuevo.</div>';
                    });
            }, 300);
        });

        resultsBox?.addEventListener('click', (e) => {
            const btn = e.target.closest('.js-pedido-client-result');
            if (!btn) return;
            const c = lastResults[parseInt(btn.dataset.idx, 10)];
            if (c) selectClient(c.id, c.name, c.phone);
        });

        // ── Crear cliente sin recargar la página ─────────────────────────
        // Reutiliza el modal/formulario de cliente EXISTENTES tal cual
        // (c-client-modal.php / c-client-form.php, con su propia validación
        // de duplicados ya wireada por av_check_user()): solo se intercepta
        // el submit para enviarlo por fetch() en vez de dejar que el
        // navegador navegue. save_contact() ya añade "?id=<nuevo_id>" a
        // "redirect_to" (mismo mecanismo que usa "Crear SAT"); como
        // redirect_to aquí es la URL limpia de esta misma página (ver
        // $client_modal_redirect_to en c-pedido-form.php), fetch() sigue la
        // redirección y expone la URL final en response.url sin que el
        // navegador llegue a navegar de verdad.
        const modal      = document.querySelector('.js-client-modal');
        const clientForm = modal?.querySelector('.c-client-form__form-form');

        clientForm?.addEventListener('submit', (e) => {
            e.preventDefault();

            const submitBtn = clientForm.querySelector('.c-client-form__save');
            const nombre = clientForm.querySelector('[name="nombre"]')?.value.trim() || '';
            const ext    = clientForm.querySelector('[name="telefono-ext"]')?.value.trim() || '';
            const phone  = clientForm.querySelector('[name="telefono"]')?.value.trim() || '';

            if (submitBtn) submitBtn.disabled = true;

            // getAttribute y no clientForm.action: el formulario tiene un
            // <input name="action">, que tapa la propiedad .action del form
            // (devolvería el <input>, no la URL).
            fetch(clientForm.getAttribute('action'), { method: 'POST', body: new FormData(clientForm) })
                .then(response => {
                    const url = new URL(response.url);
                    const newId = url.searchParams.get('id');
                    if (!newId) throw new Error('sin id');

                    selectClient(newId, nombre, (ext + ' ' + phone).trim());
                    modal.classList.remove('is-active');
                    document.body.classList.remove('is-overflow-hidden');
                    clientForm.reset();
                })
                .catch(() => {
                    alert('No se ha podido crear el cliente. Comprueba los datos e inténtalo de nuevo.');
                })
                .finally(() => {
                    if (submitBtn) submitBtn.disabled = false;
                });
        });
    };

    // ── "Deshacer" al eliminar una línea, dentro de la propia tabla ─────────
    // Compartido por las tres tablas de líneas (Pedidos, Presupuestos, SAT):
    // en vez de un aviso flotante aparte, se inserta una fila temporal justo
    // donde estaba la eliminada (mismo ancho, vía colspan), con el nombre de
    // la línea y un botón "Deshacer". Si no se pulsa en unos segundos, o se
    // borra/añade otra línea antes, desaparece sola sin tocar más datos (el
    // item ya se ha quitado de la lista real al eliminar). tbody debe estar
    // ya repintado SIN la línea eliminada cuando se llama a esto (mismo idx
    // que ocupaba antes de quitarla).
    const av_show_undo_row = (tbody, idx, colspan, name, onUndo) => {
        tbody.querySelector('.js-lines-undo-row')?.remove();

        const escapeHtml = (str) => (str || '').toString()
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');

        const tr = document.createElement('tr');
        tr.className = 'c-pedido-lines-table__undo-row js-lines-undo-row';
        tr.innerHTML = `
            <td colspan="${colspan}">
                <span class="c-pedido-lines-table__undo-text">Línea eliminada: "${escapeHtml(name) || 'sin nombre'}".</span>
                <button type="button" class="c-pedido-lines-table__undo-btn">Deshacer</button>
            </td>
        `;

        const refRow = tbody.children[idx] || null;
        tbody.insertBefore(tr, refRow);

        const timer = setTimeout(() => tr.remove(), 6000);
        tr.querySelector('.c-pedido-lines-table__undo-btn').addEventListener('click', () => {
            clearTimeout(timer);
            tr.remove();
            onUndo();
        });
    };

    // ── Pedidos: líneas del pedido ────────────────────────────────────────
    // Tabla estilo hoja de cálculo, mismo patrón que
    // av_presupuesto_lines_widget()/components/c-presupuesto-lines.php: una
    // fila de tabla por artículo (buscador de catálogo arriba + "+ Añadir
    // fila" dentro de la propia tabla para lo que no esté catalogado), en vez
    // de la tarjeta con checkbox "Producto no catalogado" que tenía antes.

    // ── SAT: tabla de "Artículos y servicios" ───────────────────────────────
    // Misma tabla de Pedidos (mismas clases c-pedido-lines-table__*, sin CSS
    // nuevo), sin columna Recepción (no aplica al SAT) y con el buscador
    // combinado Artículos + Servicios que ya usa Presupuestos
    // (av_presupuesto_lines_widget, más abajo). Sustituye a los antiguos
    // widgets de texto libre "Reparación"/"Piezas pedidas"; NO toca
    // "Artículos usados" (stock real), que sigue siendo su propio widget.
    const av_sat_lines_widget = () => {

        const widget = document.querySelector('.js-sat-lines-widget');
        if (!widget) return;

        const searchInput = widget.querySelector('.js-sat-lines-search');
        const resultsBox  = widget.querySelector('.js-sat-lines-results');
        const tbody       = widget.querySelector('.js-sat-lines-tbody');
        const addBlankBtn = widget.querySelector('.js-sat-lines-add-blank');
        const hidden      = widget.querySelector('.js-sat-lines-hidden');

        if (!tbody || !hidden) return;

        const vatChoices = JSON.parse(widget.querySelector('.js-sat-lines-vat-choices')?.textContent || '{}');
        const nonce = widget.dataset.nonce;

        const escapeHtml = (str) => (str || '').toString()
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        const fmtEUR = (n) => (parseFloat(n) || 0).toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        // Descripción autocompletada al elegir un artículo del catálogo,
        // igual que en Presupuestos.
        const buildArticuloDescription = (art) => {
            const desc  = (art.description || '').trim();
            const extra = [art.model, art.brand].map(v => (v || '').trim()).filter(Boolean).join(' ');
            if (desc && extra) return `${desc} - ${extra}`;
            if (desc) return desc;
            return extra;
        };

        const getItems = () => {
            try {
                const parsed = JSON.parse(hidden.value);
                return Array.isArray(parsed) ? parsed : [];
            } catch (e) { return []; }
        };

        const grossFromNet = (net, vat) => Math.round((parseFloat(net) || 0) * (1 + (parseFloat(vat) || 0) / 100) * 100) / 100;
        const netFromGross = (gross, vat) => Math.round((parseFloat(gross) || 0) / (1 + (parseFloat(vat) || 0) / 100) * 1e6) / 1e6;
        const round2 = (n) => Math.round((parseFloat(n) || 0) * 100) / 100;

        const calcSubtotal = (item) => {
            const net = parseFloat(item.sale_price) || 0;
            const qty = parseFloat(item.quantity) || 0;
            const base = Math.round(net * qty * 100) / 100;
            const total = Math.round(net * qty * (1 + (parseFloat(item.vat) || 0) / 100) * 100) / 100;
            return { base, total };
        };

        // "Coste final" del SAT (campo name="price" del formulario): suma de
        // los totales CON IVA de cada línea, igual que hacían los antiguos
        // widgets de Reparación/Piezas (recalcTotal() en av_repair_list()).
        // Solo existe en el detalle del SAT; en el banner del listado no hay
        // campo "price" y esto no hace nada (allí el precio se calcula al
        // guardar, ver el botón Guardar del banner en av_save_status()).
        const form       = widget.closest('.c-sat-form__form') || widget.closest('form');
        const priceField = form ? form.querySelector('[name="price"]') : null;

        const recalcPrice = (items) => {
            if (!priceField) return;
            const total = items.reduce((sum, it) => sum + calcSubtotal(it).total, 0);
            priceField.value = total > 0 ? total.toFixed(2) : '';
            priceField.dispatchEvent(new Event('input', { bubbles: true }));
        };

        const syncHidden = (items) => {
            hidden.value = JSON.stringify(items);
            hidden.dispatchEvent(new Event('input', { bubbles: true }));
            hidden.dispatchEvent(new Event('change', { bubbles: true }));
            recalcPrice(items);
        };

        const optionsHtml = (choices, selected) => Object.keys(choices).map(val =>
            `<option value="${escapeHtml(val)}" ${val === String(selected) ? 'selected' : ''}>${escapeHtml(choices[val])}</option>`
        ).join('');

        const blankLine = () => ({
            article_id: null, name_snapshot: '', sku_snapshot: '', description: '',
            quantity: 1, sale_price: 0, vat: 21, supplier: '',
        });

        const renderRow = (item, idx) => {
            const tr = document.createElement('tr');
            const subtotal = calcSubtotal(item);
            const grossPrice = grossFromNet(item.sale_price, item.vat);

            tr.innerHTML = `
                <td><input type="text" class="js-sl-field c-pedido-lines-table__input" data-field="name_snapshot" data-idx="${idx}" value="${escapeHtml(item.name_snapshot)}" placeholder="Nombre"></td>
                <td><input type="text" class="js-sl-field c-pedido-lines-table__input" data-field="description" data-idx="${idx}" value="${escapeHtml(item.description)}" placeholder="Descripción"></td>
                <td><input type="number" step="any" min="0.01" class="js-sl-field c-pedido-lines-table__input c-pedido-lines-table__input--qty" data-field="quantity" data-idx="${idx}" value="${item.quantity ?? 1}"></td>
                <td><select class="js-sl-field c-pedido-lines-table__input" data-field="vat" data-idx="${idx}">${optionsHtml(vatChoices, item.vat)}</select></td>
                <td><input type="number" step="0.01" min="0" class="js-sl-price-net c-pedido-lines-table__input c-pedido-lines-table__input--price" data-idx="${idx}" value="${round2(item.sale_price)}"></td>
                <td><input type="number" step="0.01" min="0" class="js-sl-price-gross c-pedido-lines-table__input c-pedido-lines-table__input--price" data-idx="${idx}" value="${grossPrice}"></td>
                <td class="c-pedido-lines-table__readonly c-pedido-lines-table__subtotal"><span class="js-sl-subtotal" data-idx="${idx}">${fmtEUR(subtotal.total)} €</span></td>
                <td class="c-pedido-lines-table__supplier-cell">
                    <input type="text" class="js-sl-field js-sl-supplier c-pedido-lines-table__input" data-field="supplier" data-idx="${idx}" value="${escapeHtml(item.supplier)}" autocomplete="off">
                    <div class="c-pedido-lines-table__supplier-suggestions js-sl-supplier-suggestions is-hidden" data-idx="${idx}"></div>
                </td>
                <td><button type="button" class="c-pedido-lines-table__remove js-sat-lines-remove" data-idx="${idx}" aria-label="Eliminar">×</button></td>
            `;
            return tr;
        };

        const renderList = (items) => {
            if (!items.length) {
                tbody.innerHTML = `<tr class="c-pedido-lines-table__empty-row"><td colspan="9">Todavía no hay ningún artículo ni servicio. Búscalo arriba o añade una fila personalizada.</td></tr>`;
                return;
            }
            tbody.innerHTML = '';
            items.forEach((item, idx) => tbody.appendChild(renderRow(item, idx)));
        };

        renderList(getItems());

        const addLine = (line) => {
            const items = getItems();
            items.push(line);
            syncHidden(items);
            renderList(items);
        };

        addBlankBtn?.addEventListener('click', () => {
            addLine(blankLine());
            const lastNameInput = tbody.querySelector('tr:last-child [data-field="name_snapshot"]');
            lastNameInput?.focus();
        });

        // ── Buscador combinado: Artículos + Servicios a la vez ─────────────
        let debounceId = null;
        let lastResults = [];

        const fetchJson = (action, actionNonce, term) => {
            const formData = new FormData();
            formData.append('action', action);
            formData.append('nonce', actionNonce);
            formData.append('term', term);
            return fetch(av_data.av_ajax_url, { method: 'POST', body: formData })
                .then(r => r.json())
                .catch(() => null);
        };

        searchInput?.addEventListener('input', () => {
            const term = searchInput.value.trim();
            if (debounceId) clearTimeout(debounceId);

            if (term.length < 2) {
                resultsBox.innerHTML = '';
                resultsBox.classList.add('is-hidden');
                return;
            }

            debounceId = setTimeout(() => {
                Promise.all([
                    fetchJson('av_ajax_search_articulos_picker', av_data.nonce_articulos_picker, term),
                    fetchJson('av_ajax_search_servicios_picker', av_data.nonce_servicios_picker, term),
                ]).then(([artRes, servRes]) => {
                    const articulos = (artRes && artRes.data) || [];
                    const servicios = (servRes && servRes.data) || [];

                    lastResults = [
                        ...articulos.map(a => ({ ...a, _type: 'articulo' })),
                        ...servicios.map(s => ({ ...s, _type: 'servicio' })),
                    ];

                    resultsBox.classList.remove('is-hidden');

                    if (!lastResults.length) {
                        resultsBox.innerHTML = '<div class="c-sat-client-picker__no-results">No se ha encontrado ningún artículo ni servicio.</div>';
                        return;
                    }

                    resultsBox.innerHTML = lastResults.map((r, idx) => {
                        const isArticulo = r._type === 'articulo';
                        const price = isArticulo ? (r.unit_price || 0) : (r.price || 0);
                        const metaParts = isArticulo
                            ? [r.sku, r.category, fmtEUR(price) + ' €']
                            : [fmtEUR(price) + ' €'];
                        return `
                        <button type="button" class="c-sat-client-picker__result js-sat-lines-result" data-idx="${idx}">
                            <span class="c-sat-client-picker__result-name"><span class="c-presupuesto-table__result-type c-presupuesto-table__result-type--${r._type}">${isArticulo ? 'Artículo' : 'Servicio'}</span>${escapeHtml(r.name)}</span>
                            <span class="c-sat-client-picker__result-meta">${escapeHtml(metaParts.filter(Boolean).join(' · '))}</span>
                        </button>
                    `;
                    }).join('');
                });
            }, 300);
        });

        resultsBox?.addEventListener('click', (e) => {
            const btn = e.target.closest('.js-sat-lines-result');
            if (!btn) return;
            const r = lastResults[parseInt(btn.dataset.idx, 10)];
            if (!r) return;

            if (r._type === 'articulo') {
                addLine({
                    article_id: r.id,
                    name_snapshot: r.name,
                    sku_snapshot: r.sku,
                    description: buildArticuloDescription(r),
                    quantity: 1,
                    sale_price: r.unit_price || 0,
                    vat: r.vat || 21,
                    supplier: '',
                });
            } else {
                // Un servicio no tiene SKU ni IVA propios en su catálogo: el
                // precio guardado en Servicios ya incluye IVA (misma
                // convención que tenía "Reparación"), así que se convierte a
                // precio sin IVA con el 21% por defecto.
                const vat = 21;
                const net = Math.round(((r.price || 0) / (1 + vat / 100)) * 100) / 100;
                addLine({
                    article_id: null,
                    name_snapshot: r.name,
                    sku_snapshot: '',
                    description: r.description || '',
                    quantity: 1,
                    sale_price: net,
                    vat,
                    supplier: '',
                });
            }

            searchInput.value = '';
            resultsBox.innerHTML = '';
            resultsBox.classList.add('is-hidden');
        });

        // ── Eliminar línea (con "Deshacer") ─────────────────────────────────
        tbody.addEventListener('click', (e) => {
            const btn = e.target.closest('.js-sat-lines-remove');
            if (!btn) return;
            const idx = parseInt(btn.dataset.idx, 10);
            const items = getItems();
            const removed = items[idx];
            items.splice(idx, 1);
            syncHidden(items);
            renderList(items);

            if (removed) {
                av_show_undo_row(tbody, idx, 9, removed.name_snapshot, () => {
                    const current = getItems();
                    current.splice(idx, 0, removed);
                    syncHidden(current);
                    renderList(current);
                });
            }
        });

        // ── Edición en vivo de cualquier celda ─────────────────────────────
        const refreshRowDisplay = (idx, items) => {
            const item = items[idx];
            if (!item) return;
            const subtotal = calcSubtotal(item);
            const grossPrice = grossFromNet(item.sale_price, item.vat);

            const netInput   = tbody.querySelector(`.js-sl-price-net[data-idx="${idx}"]`);
            const grossInput = tbody.querySelector(`.js-sl-price-gross[data-idx="${idx}"]`);
            if (netInput   && document.activeElement !== netInput)   netInput.value = round2(item.sale_price);
            if (grossInput && document.activeElement !== grossInput) grossInput.value = grossPrice;

            const subtotalEl = tbody.querySelector(`.js-sl-subtotal[data-idx="${idx}"]`);
            if (subtotalEl) subtotalEl.textContent = `${fmtEUR(subtotal.total)} €`;
        };

        tbody.addEventListener('input', (e) => {
            const target = e.target;
            const idx = parseInt(target.dataset.idx, 10);
            const items = getItems();
            if (!items[idx]) return;

            if (target.classList.contains('js-sl-price-net')) {
                items[idx].sale_price = target.value === '' ? 0 : parseFloat(String(target.value).replace(',', '.'));
            } else if (target.classList.contains('js-sl-price-gross')) {
                const gross = target.value === '' ? 0 : parseFloat(String(target.value).replace(',', '.'));
                items[idx].sale_price = netFromGross(gross, items[idx].vat);
            } else if (target.classList.contains('js-sl-field')) {
                const key = target.dataset.field;
                if (key === 'quantity' || key === 'vat') {
                    items[idx][key] = target.value === '' ? 0 : parseFloat(String(target.value).replace(',', '.'));
                } else {
                    items[idx][key] = target.value;
                }
                if (key === 'supplier') handleSupplierSuggestions(target, idx);
            } else {
                return;
            }

            syncHidden(items);
            refreshRowDisplay(idx, items);
        });

        // ── Autocompletado de proveedor (mismo endpoint que Pedidos: sugiere
        // proveedores ya usados en el catálogo de Artículos) ───────────────
        let supplierDebounceId = null;
        const handleSupplierSuggestions = (input, idx) => {
            const box = tbody.querySelector(`.js-sl-supplier-suggestions[data-idx="${idx}"]`);
            if (!box) return;
            const term = input.value.trim();
            if (supplierDebounceId) clearTimeout(supplierDebounceId);
            if (term.length < 2) {
                box.innerHTML = '';
                box.classList.add('is-hidden');
                return;
            }
            supplierDebounceId = setTimeout(() => {
                const formData = new FormData();
                formData.append('action', 'av_ajax_pedido_supplier_suggestions');
                formData.append('nonce', nonce);
                formData.append('term', term);

                fetch(av_data.av_ajax_url, { method: 'POST', body: formData })
                    .then(r => r.json())
                    .then(results => {
                        const suggestions = (results && results.data) || [];
                        if (!suggestions.length) {
                            box.innerHTML = '';
                            box.classList.add('is-hidden');
                            return;
                        }
                        box.classList.remove('is-hidden');
                        box.innerHTML = suggestions.map(s => `<button type="button" class="c-pedido-lines-table__supplier-suggestion js-sl-supplier-pick" data-idx="${idx}" data-value="${escapeHtml(s)}">${escapeHtml(s)}</button>`).join('');
                    })
                    .catch(() => {});
            }, 300);
        };

        tbody.addEventListener('click', (e) => {
            const btn = e.target.closest('.js-sl-supplier-pick');
            if (!btn) return;
            const idx = parseInt(btn.dataset.idx, 10);
            const items = getItems();
            if (!items[idx]) return;
            items[idx].supplier = btn.dataset.value;
            syncHidden(items);
            const input = tbody.querySelector(`.js-sl-supplier[data-idx="${idx}"]`);
            if (input) input.value = btn.dataset.value;
            const box = tbody.querySelector(`.js-sl-supplier-suggestions[data-idx="${idx}"]`);
            if (box) { box.innerHTML = ''; box.classList.add('is-hidden'); }
        });

        // Restaurar desde el botón "Cancelar" del banner del listado de SATs
        // (si este widget vive ahí) — mismo patrón que av_repair_list().
        hidden.addEventListener('av:repair-reset', () => {
            renderList(getItems());
        });
    };

    const av_pedido_lines_widget = () => {

        const widget = document.querySelector('.js-pedido-lines-widget');
        if (!widget) return;

        const searchInput = widget.querySelector('.js-pedido-line-search');
        const resultsBox  = widget.querySelector('.js-pedido-line-results');
        const tbody       = widget.querySelector('.js-pedido-lines-tbody');
        const addBlankBtn = widget.querySelector('.js-pedido-line-add-blank');
        const hidden      = widget.querySelector('.js-pedido-lines-hidden');
        const errorMsg    = widget.querySelector('.js-pedido-lines-error');

        if (!tbody || !hidden) return;

        const vatChoices = JSON.parse(widget.querySelector('.js-pedido-vat-choices')?.textContent || '{}');

        // "Recepción" por línea (solo al editar un pedido ya guardado): la
        // acción AJAX trabaja por ÍNDICE de línea en la base de datos, así que
        // solo se ofrece mientras la estructura de líneas no haya cambiado
        // (sin añadir/quitar filas sin guardar), y se actualiza received_date
        // también en el JSON del formulario para que un "Guardar" posterior no
        // lo pise con vacío.
        const canReceive = widget.dataset.canReceive === '1';
        const receivePedidoId = widget.dataset.pedidoId;
        const receiveNonce = widget.dataset.nonce;
        let structureDirty = false;
        let savedCount = 0;
        const fmtYmd = (ymd) => { const m = /^(\d{4})-(\d{2})-(\d{2})$/.exec(ymd || ''); return m ? `${m[3]}/${m[2]}/${m[1]}` : (ymd || ''); };
        const nonceField = document.querySelector('.js-pedido-form [name="nonce"]');

        const summaryCount = document.querySelector('.js-pedido-summary-count');
        const summaryTotal = document.querySelector('.js-pedido-summary-total');

        const escapeHtml = (str) => (str || '').toString()
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        const fmtEUR = (n) => (parseFloat(n) || 0).toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        const getItems = () => {
            try {
                const parsed = JSON.parse(hidden.value);
                return Array.isArray(parsed) ? parsed : [];
            } catch (e) { return []; }
        };

        // Mismos campos y mismas fórmulas que av_presupuesto_lines_widget()
        // (Sin IVA/Con IVA autocalculado en los dos sentidos, subtotal CON
        // IVA) — ver av_pedido_line_subtotal() en inc/pedidos/crud.php.
        const grossFromNet = (net, vat) => Math.round((parseFloat(net) || 0) * (1 + (parseFloat(vat) || 0) / 100) * 100) / 100;
        // 6 decimales (no 2): al escribir el precio CON IVA hay que poder recuperar
        // exactamente ese importe al volver a aplicar el IVA (con la base a 2
        // decimales, 29,99 acababa siendo 30,00). Ver av_line_amounts() en PHP.
        const netFromGross = (gross, vat) => Math.round((parseFloat(gross) || 0) / (1 + (parseFloat(vat) || 0) / 100) * 1e6) / 1e6;
        const round2 = (n) => Math.round((parseFloat(n) || 0) * 100) / 100;

        const calcSubtotal = (item) => {
            const net = parseFloat(item.sale_price) || 0;
            const qty = parseFloat(item.quantity) || 0;
            const base = Math.round(net * qty * 100) / 100;
            const total = Math.round(net * qty * (1 + (parseFloat(item.vat) || 0) / 100) * 100) / 100;
            return { base, total };
        };

        const updateSummary = (items) => {
            if (summaryCount) summaryCount.textContent = items.length;
            if (summaryTotal) {
                const total = items.reduce((sum, it) => sum + calcSubtotal(it).total, 0);
                summaryTotal.textContent = fmtEUR(total) + ' €';
            }
        };

        const syncHidden = (items) => {
            hidden.value = JSON.stringify(items);
            hidden.dispatchEvent(new Event('input', { bubbles: true }));
            hidden.dispatchEvent(new Event('change', { bubbles: true }));
            if (items.length) errorMsg?.classList.add('is-hidden');
            updateSummary(items);
        };

        const optionsHtml = (choices, selected) => Object.keys(choices).map(val =>
            `<option value="${escapeHtml(val)}" ${val === String(selected) ? 'selected' : ''}>${escapeHtml(choices[val])}</option>`
        ).join('');

        const blankLine = () => ({
            article_id: null, name_snapshot: '', sku_snapshot: '', description: '',
            quantity: 1, sale_price: 0, vat: 21, supplier: '', received_date: '',
        });

        const renderRow = (item, idx) => {
            const tr = document.createElement('tr');
            const subtotal = calcSubtotal(item);
            const grossPrice = grossFromNet(item.sale_price, item.vat);

            let receiveCell = '';
            if (canReceive) {
                if (item.received_date) {
                    receiveCell = `<td><span class="c-pedido-lines-table__received">Recibido ${escapeHtml(fmtYmd(item.received_date))}</span></td>`;
                } else if (structureDirty || idx >= savedCount) {
                    receiveCell = `<td><span class="c-pedido-lines-table__hint" title="Guarda el pedido para poder marcar esta línea como recibida">—</span></td>`;
                } else {
                    receiveCell = `<td><button type="button" class="o-button o-button--style-2 c-pedido-lines-table__receive js-pedido-line-receive" data-idx="${idx}">Marcar recibido</button></td>`;
                }
            }

            tr.innerHTML = `
                <td><input type="text" class="js-pl-field c-pedido-lines-table__input" data-field="name_snapshot" data-idx="${idx}" value="${escapeHtml(item.name_snapshot)}" placeholder="Nombre"></td>
                <td><input type="text" class="js-pl-field c-pedido-lines-table__input" data-field="description" data-idx="${idx}" value="${escapeHtml(item.description)}" placeholder="Descripción"></td>
                <td><input type="number" step="any" min="0.01" class="js-pl-field c-pedido-lines-table__input c-pedido-lines-table__input--qty" data-field="quantity" data-idx="${idx}" value="${item.quantity ?? 1}"></td>
                <td><select class="js-pl-field c-pedido-lines-table__input" data-field="vat" data-idx="${idx}">${optionsHtml(vatChoices, item.vat)}</select></td>
                <td><input type="number" step="0.01" min="0" class="js-pl-price-net c-pedido-lines-table__input c-pedido-lines-table__input--price" data-idx="${idx}" value="${round2(item.sale_price)}"></td>
                <td><input type="number" step="0.01" min="0" class="js-pl-price-gross c-pedido-lines-table__input c-pedido-lines-table__input--price" data-idx="${idx}" value="${grossPrice}"></td>
                <td class="c-pedido-lines-table__readonly c-pedido-lines-table__subtotal"><span class="js-pl-subtotal" data-idx="${idx}">${fmtEUR(subtotal.total)} €</span></td>
                <td class="c-pedido-lines-table__supplier-cell">
                    <input type="text" class="js-pl-field js-pl-supplier c-pedido-lines-table__input" data-field="supplier" data-idx="${idx}" value="${escapeHtml(item.supplier)}" autocomplete="off">
                    <div class="c-pedido-lines-table__supplier-suggestions js-pl-supplier-suggestions is-hidden" data-idx="${idx}"></div>
                </td>
                ${receiveCell}
                <td><button type="button" class="c-pedido-lines-table__remove js-pedido-line-remove" data-idx="${idx}" aria-label="Eliminar">×</button></td>
            `;
            return tr;
        };

        const renderList = (items) => {
            if (!items.length) {
                tbody.innerHTML = `<tr class="c-pedido-lines-table__empty-row"><td colspan="${canReceive ? 10 : 9}">Todavía no hay ninguna línea. Busca un artículo arriba o añade una fila personalizada.</td></tr>`;
                return;
            }
            tbody.innerHTML = '';
            items.forEach((item, idx) => tbody.appendChild(renderRow(item, idx)));
        };

        savedCount = getItems().length;
        renderList(getItems());
        updateSummary(getItems());

        const addLine = (line) => {
            structureDirty = true;
            const items = getItems();
            items.push(line);
            syncHidden(items);
            renderList(items);
        };

        addBlankBtn?.addEventListener('click', () => {
            addLine(blankLine());
            const lastNameInput = tbody.querySelector('tr:last-child [data-field="name_snapshot"]');
            lastNameInput?.focus();
        });

        // ── Añadir vía catálogo ────────────────────────────────────────────
        let debounceId = null;
        let lastResults = [];

        searchInput?.addEventListener('input', () => {
            const term = searchInput.value.trim();
            if (debounceId) clearTimeout(debounceId);

            if (term.length < 2) {
                resultsBox.innerHTML = '';
                resultsBox.classList.add('is-hidden');
                return;
            }

            debounceId = setTimeout(() => {
                const formData = new FormData();
                formData.append('action', 'av_ajax_search_articulos_picker');
                formData.append('nonce', av_data.nonce_articulos_picker);
                formData.append('term', term);

                fetch(av_data.av_ajax_url, { method: 'POST', body: formData })
                    .then(response => response.json())
                    .then(results => {
                        lastResults = (results && results.data) || [];
                        resultsBox.classList.remove('is-hidden');

                        if (!lastResults.length) {
                            resultsBox.innerHTML = '<div class="c-sat-client-picker__no-results">No se ha encontrado ningún artículo.</div>';
                            return;
                        }

                        resultsBox.innerHTML = lastResults.map((art, idx) => {
                            // "SKU · Categoría · Precio" (categoría solo si el
                            // artículo tiene una asignada — ver 'category' en
                            // av_ajax_search_articulos_picker(), inc/articulos/sat-integration.php).
                            const metaParts = [art.sku, art.category, fmtEUR(art.unit_price) + ' €'].filter(Boolean);
                            return `
                            <button type="button" class="c-sat-client-picker__result js-pedido-line-result" data-idx="${idx}">
                                <span class="c-sat-client-picker__result-name">${escapeHtml(art.name)}</span>
                                <span class="c-sat-client-picker__result-meta">${escapeHtml(metaParts.join(' · '))}</span>
                            </button>
                        `;
                        }).join('');
                    })
                    .catch(() => {
                        resultsBox.innerHTML = '<div class="c-sat-client-picker__no-results">Error de conexión. Inténtalo de nuevo.</div>';
                    });
            }, 300);
        });

        resultsBox?.addEventListener('click', (e) => {
            const btn = e.target.closest('.js-pedido-line-result');
            if (!btn) return;
            const art = lastResults[parseInt(btn.dataset.idx, 10)];
            if (!art) return;

            addLine({
                article_id: art.id,
                name_snapshot: art.name,
                sku_snapshot: art.sku,
                description: '',
                quantity: 1,
                sale_price: art.unit_price || 0,
                vat: art.vat || 21,
                supplier: '',
                received_date: '',
            });

            searchInput.value = '';
            resultsBox.innerHTML = '';
            resultsBox.classList.add('is-hidden');
        });

        // ── Eliminar línea (con "Deshacer") ─────────────────────────────────
        tbody.addEventListener('click', (e) => {
            const btn = e.target.closest('.js-pedido-line-remove');
            if (!btn) return;
            structureDirty = true;
            const idx = parseInt(btn.dataset.idx, 10);
            const items = getItems();
            const removed = items[idx];
            items.splice(idx, 1);
            syncHidden(items);
            renderList(items);

            if (removed) {
                av_show_undo_row(tbody, idx, canReceive ? 10 : 9, removed.name_snapshot, () => {
                    const current = getItems();
                    current.splice(idx, 0, removed);
                    syncHidden(current);
                    renderList(current);
                });
            }
        });

        // ── Marcar línea como recibida (AJAX, entra stock si es de catálogo) ─
        tbody.addEventListener('click', (e) => {
            const btn = e.target.closest('.js-pedido-line-receive');
            if (!btn) return;
            const idx = parseInt(btn.dataset.idx, 10);

            const formData = new FormData();
            formData.append('action', 'av_ajax_pedido_line_received');
            formData.append('id', receivePedidoId);
            formData.append('line_index', idx);
            formData.append('nonce', receiveNonce);

            btn.disabled = true;
            btn.textContent = 'Marcando...';

            fetch(av_data.av_ajax_url, { method: 'POST', body: formData, credentials: 'same-origin' })
                .then(res => res.json())
                .then(json => {
                    if (!json || !json.success) throw new Error('line received failed');
                    const data = json.data;

                    const items = getItems();
                    if (items[idx]) items[idx].received_date = data.received_date;
                    syncHidden(items);
                    renderList(items);

                    // Si todas las líneas quedan recibidas el servidor promociona el
                    // pedido a "recibido": se refleja en el desplegable de estado
                    // para que un Guardar posterior no lo revierta.
                    const statusSelect = document.querySelector('.js-pedido-form [name="status"]');
                    if (statusSelect && data.pedido_status && statusSelect.querySelector(`option[value="${data.pedido_status}"]`)) {
                        statusSelect.value = data.pedido_status;
                    }
                })
                .catch(() => {
                    btn.disabled = false;
                    btn.textContent = 'Marcar recibido';
                    alert('No se ha podido marcar la línea como recibida. Inténtalo de nuevo.');
                });
        });

        // ── Edición en vivo de cualquier celda ─────────────────────────────
        // Repinta SOLO el texto de subtotal y el otro campo de precio (no el
        // input que se está escribiendo), para no perder el foco ni el
        // cursor mientras se escribe en una fila.
        const refreshRowDisplay = (idx, items) => {
            const item = items[idx];
            if (!item) return;
            const subtotal = calcSubtotal(item);
            const grossPrice = grossFromNet(item.sale_price, item.vat);

            const netInput   = tbody.querySelector(`.js-pl-price-net[data-idx="${idx}"]`);
            const grossInput = tbody.querySelector(`.js-pl-price-gross[data-idx="${idx}"]`);
            if (netInput   && document.activeElement !== netInput)   netInput.value = round2(item.sale_price);
            if (grossInput && document.activeElement !== grossInput) grossInput.value = grossPrice;

            const subtotalEl = tbody.querySelector(`.js-pl-subtotal[data-idx="${idx}"]`);
            if (subtotalEl) subtotalEl.textContent = `${fmtEUR(subtotal.total)} €`;
        };

        tbody.addEventListener('input', (e) => {
            const target = e.target;
            const idx = parseInt(target.dataset.idx, 10);
            const items = getItems();
            if (!items[idx]) return;

            if (target.classList.contains('js-pl-price-net')) {
                items[idx].sale_price = target.value === '' ? 0 : parseFloat(String(target.value).replace(',', '.'));
            } else if (target.classList.contains('js-pl-price-gross')) {
                const gross = target.value === '' ? 0 : parseFloat(String(target.value).replace(',', '.'));
                items[idx].sale_price = netFromGross(gross, items[idx].vat);
            } else if (target.classList.contains('js-pl-field')) {
                const key = target.dataset.field;
                if (key === 'quantity' || key === 'vat') {
                    items[idx][key] = target.value === '' ? 0 : parseFloat(String(target.value).replace(',', '.'));
                } else {
                    items[idx][key] = target.value;
                }
                if (key === 'supplier') handleSupplierSuggestions(target, idx);
            } else {
                return;
            }

            syncHidden(items);
            refreshRowDisplay(idx, items);
        });

        // ── Autocompletado de proveedor (sugiere valores ya usados en
        // Artículos, sin crear ningún catálogo nuevo) ─────────────────────
        let supplierDebounceId = null;
        const handleSupplierSuggestions = (input, idx) => {
            const box = tbody.querySelector(`.js-pl-supplier-suggestions[data-idx="${idx}"]`);
            if (!box) return;
            const term = input.value.trim();
            if (supplierDebounceId) clearTimeout(supplierDebounceId);
            if (term.length < 2) {
                box.innerHTML = '';
                box.classList.add('is-hidden');
                return;
            }
            supplierDebounceId = setTimeout(() => {
                const formData = new FormData();
                formData.append('action', 'av_ajax_pedido_supplier_suggestions');
                formData.append('nonce', nonceField.value);
                formData.append('term', term);

                fetch(av_data.av_ajax_url, { method: 'POST', body: formData })
                    .then(r => r.json())
                    .then(results => {
                        const suggestions = (results && results.data) || [];
                        if (!suggestions.length) {
                            box.innerHTML = '';
                            box.classList.add('is-hidden');
                            return;
                        }
                        box.classList.remove('is-hidden');
                        box.innerHTML = suggestions.map(s => `<button type="button" class="c-pedido-lines-table__supplier-suggestion js-pl-supplier-pick" data-idx="${idx}" data-value="${escapeHtml(s)}">${escapeHtml(s)}</button>`).join('');
                    })
                    .catch(() => {});
            }, 300);
        };

        tbody.addEventListener('click', (e) => {
            const btn = e.target.closest('.js-pl-supplier-pick');
            if (!btn) return;
            const idx = parseInt(btn.dataset.idx, 10);
            const items = getItems();
            if (!items[idx]) return;
            items[idx].supplier = btn.dataset.value;
            syncHidden(items);
            const input = tbody.querySelector(`.js-pl-supplier[data-idx="${idx}"]`);
            if (input) input.value = btn.dataset.value;
            const box = tbody.querySelector(`.js-pl-supplier-suggestions[data-idx="${idx}"]`);
            if (box) { box.innerHTML = ''; box.classList.add('is-hidden'); }
        });

        // ── Validación mínima en el frontend (el backend también la aplica) ──
        const form = widget.closest('form');
        form?.addEventListener('submit', (e) => {
            if (getItems().length === 0) {
                e.preventDefault();
                errorMsg?.classList.remove('is-hidden');
                errorMsg?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    };

    // ── Presupuestos: selector de cliente ──────────────────────────────────
    // Clon exacto de av_pedido_client_picker(), con los mismos selectores
    // pero con el prefijo "presupuesto" (el cliente es opcional en un
    // presupuesto, igual que en un pedido).
    const av_presupuesto_client_picker = () => {

        const form = document.querySelector('.js-presupuesto-form');
        if (!form) return;

        const hiddenId      = form.querySelector('.js-presupuesto-client-id');
        const selectedBox   = form.querySelector('.js-presupuesto-client-selected');
        const nameEl        = form.querySelector('.js-presupuesto-client-name');
        const metaEl        = form.querySelector('.js-presupuesto-client-meta');
        const changeBtn     = form.querySelector('.js-presupuesto-client-change');
        const searchWrap    = form.querySelector('.js-presupuesto-client-search-wrap');
        const searchInput   = form.querySelector('.js-presupuesto-client-search');
        const resultsBox    = form.querySelector('.js-presupuesto-client-results');
        const summaryClient = form.querySelector('.js-presupuesto-summary-client');

        if (!hiddenId || !searchInput) return;

        const escapeHtml = (str) => (str || '').toString()
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');

        const selectClient = (id, name, phone) => {
            hiddenId.value = id;
            if (nameEl) nameEl.textContent = name || '';
            if (metaEl) metaEl.textContent = phone || '';
            if (summaryClient) summaryClient.textContent = name || '';
            selectedBox?.classList.remove('is-hidden');
            searchWrap?.classList.add('is-hidden');
            if (resultsBox) { resultsBox.innerHTML = ''; resultsBox.classList.add('is-hidden'); }
            searchInput.value = '';
        };

        changeBtn?.addEventListener('click', () => {
            searchWrap?.classList.remove('is-hidden');
            searchInput.focus();
        });

        let debounceId = null;
        let lastResults = [];

        searchInput.addEventListener('input', () => {
            const term = searchInput.value.trim();
            if (debounceId) clearTimeout(debounceId);

            if (term.length < 2) {
                resultsBox.innerHTML = '';
                resultsBox.classList.add('is-hidden');
                return;
            }

            debounceId = setTimeout(() => {
                const formData = new FormData();
                formData.append('action', 'av_ajax_search_clients_picker');
                formData.append('term', term);

                fetch(av_data.av_ajax_url, { method: 'POST', body: formData })
                    .then(response => response.json())
                    .then(results => {
                        lastResults = (results && results.data && results.data.clients) || [];
                        resultsBox.classList.remove('is-hidden');

                        if (!lastResults.length) {
                            resultsBox.innerHTML = '<div class="c-sat-client-picker__no-results">No se ha encontrado ningún cliente.</div>';
                            return;
                        }

                        resultsBox.innerHTML = lastResults.map((c, idx) => `
                            <button type="button" class="c-sat-client-picker__result js-presupuesto-client-result" data-idx="${idx}">
                                <span class="c-sat-client-picker__result-name">${escapeHtml(c.name)}</span>
                                <span class="c-sat-client-picker__result-meta">${escapeHtml(c.dni)}${c.phone ? ' · ' + escapeHtml(c.phone) : ''}</span>
                            </button>
                        `).join('');
                    })
                    .catch(() => {
                        resultsBox.innerHTML = '<div class="c-sat-client-picker__no-results">Error de conexión. Inténtalo de nuevo.</div>';
                    });
            }, 300);
        });

        resultsBox?.addEventListener('click', (e) => {
            const btn = e.target.closest('.js-presupuesto-client-result');
            if (!btn) return;
            const c = lastResults[parseInt(btn.dataset.idx, 10)];
            if (c) selectClient(c.id, c.name, c.phone);
        });

        // ── Crear cliente sin recargar la página (mismo mecanismo que Pedidos) ──
        const modal      = document.querySelector('.js-client-modal');
        const clientForm = modal?.querySelector('.c-client-form__form-form');

        clientForm?.addEventListener('submit', (e) => {
            e.preventDefault();

            const submitBtn = clientForm.querySelector('.c-client-form__save');
            const nombre = clientForm.querySelector('[name="nombre"]')?.value.trim() || '';
            const ext    = clientForm.querySelector('[name="telefono-ext"]')?.value.trim() || '';
            const phone  = clientForm.querySelector('[name="telefono"]')?.value.trim() || '';

            if (submitBtn) submitBtn.disabled = true;

            // getAttribute y no clientForm.action: el formulario tiene un
            // <input name="action">, que tapa la propiedad .action del form
            // (devolvería el <input>, no la URL).
            fetch(clientForm.getAttribute('action'), { method: 'POST', body: new FormData(clientForm) })
                .then(response => {
                    const url = new URL(response.url);
                    const newId = url.searchParams.get('id');
                    if (!newId) throw new Error('sin id');

                    selectClient(newId, nombre, (ext + ' ' + phone).trim());
                    modal.classList.remove('is-active');
                    document.body.classList.remove('is-overflow-hidden');
                    clientForm.reset();
                })
                .catch(() => {
                    alert('No se ha podido crear el cliente. Comprueba los datos e inténtalo de nuevo.');
                })
                .finally(() => {
                    if (submitBtn) submitBtn.disabled = false;
                });
        });
    };

    // ── Presupuestos: tabla de líneas (estilo hoja de cálculo) ──────────────
    // Cada fila es un artículo o servicio (catalogado o no) con SKU/nombre/
    // descripción/cantidad/IVA/precio editables directamente en la celda. El
    // buscador consulta a la vez Artículos (av_ajax_search_articulos_picker)
    // y Servicios (av_ajax_search_servicios_picker), sin tocar ninguno de los
    // dos endpoints; "+ Añadir fila" crea una fila en blanco para lo que no
    // esté en ningún catálogo. El precio tiene dos columnas sincronizadas
    // (con IVA / sin IVA): se puede escribir cualquiera de las dos y la otra
    // se recalcula sola. Lo que se guarda en sale_price es SIEMPRE el precio
    // sin IVA (misma convención que Artículos/Pedidos).
    const av_presupuesto_lines_widget = () => {

        const widget = document.querySelector('.js-presupuesto-lines-widget');
        if (!widget) return;

        const searchInput  = widget.querySelector('.js-presupuesto-line-search');
        const resultsBox   = widget.querySelector('.js-presupuesto-line-results');
        const addBlankBtn  = widget.querySelector('.js-presupuesto-line-add-blank');
        const tbody        = widget.querySelector('.js-presupuesto-lines-tbody');
        const hidden       = widget.querySelector('.js-presupuesto-lines-hidden');
        const errorMsg     = widget.querySelector('.js-presupuesto-lines-error');

        if (!tbody || !hidden) return;

        const vatChoices = JSON.parse(widget.querySelector('.js-presupuesto-vat-choices')?.textContent || '{}');

        const summaryCount = document.querySelector('.js-presupuesto-summary-count');
        const summaryTotal = document.querySelector('.js-presupuesto-summary-total');

        const escapeHtml = (str) => (str || '').toString()
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        const fmtEUR = (n) => (parseFloat(n) || 0).toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        // Descripción autocompletada al elegir un artículo del catálogo:
        // "Descripción - Modelo Fabricante". El guión solo se pone si hay
        // descripción; si no la hay pero sí modelo y/o fabricante, se
        // añaden solos, sin guión.
        const buildArticuloDescription = (art) => {
            const desc  = (art.description || '').trim();
            const extra = [art.model, art.brand].map(v => (v || '').trim()).filter(Boolean).join(' ');
            if (desc && extra) return `${desc} - ${extra}`;
            if (desc) return desc;
            return extra;
        };

        const getItems = () => {
            try {
                const parsed = JSON.parse(hidden.value);
                return Array.isArray(parsed) ? parsed : [];
            } catch (e) { return []; }
        };

        // Con IVA = sin IVA * (1 + vat/100); sin IVA = con IVA / (1 + vat/100).
        // Mismas fórmulas que av_presupuesto_build_data() en inc/presupuestos/pdf.php.
        const grossFromNet = (net, vat) => Math.round((parseFloat(net) || 0) * (1 + (parseFloat(vat) || 0) / 100) * 100) / 100;
        // 6 decimales (no 2): al escribir el precio CON IVA hay que poder recuperar
        // exactamente ese importe al volver a aplicar el IVA (con la base a 2
        // decimales, 29,99 acababa siendo 30,00). Ver av_line_amounts() en PHP.
        const netFromGross = (gross, vat) => Math.round((parseFloat(gross) || 0) / (1 + (parseFloat(vat) || 0) / 100) * 1e6) / 1e6;
        const round2 = (n) => Math.round((parseFloat(n) || 0) * 100) / 100;

        const calcSubtotal = (item) => {
            const net = parseFloat(item.sale_price) || 0;
            const qty = parseFloat(item.quantity) || 0;
            const base = Math.round(net * qty * 100) / 100;
            const total = Math.round(net * qty * (1 + (parseFloat(item.vat) || 0) / 100) * 100) / 100;
            return { base, total };
        };

        const updateSummary = (items) => {
            if (summaryCount) summaryCount.textContent = items.length;
            if (summaryTotal) {
                const total = items.reduce((sum, it) => sum + calcSubtotal(it).total, 0);
                summaryTotal.textContent = fmtEUR(total) + ' €';
            }
        };

        const syncHidden = (items) => {
            hidden.value = JSON.stringify(items);
            hidden.dispatchEvent(new Event('input', { bubbles: true }));
            hidden.dispatchEvent(new Event('change', { bubbles: true }));
            if (items.length) errorMsg?.classList.add('is-hidden');
            updateSummary(items);
        };

        const optionsHtml = (choices, selected) => Object.keys(choices).map(val =>
            `<option value="${escapeHtml(val)}" ${val === String(selected) ? 'selected' : ''}>${escapeHtml(choices[val])}</option>`
        ).join('');

        const renderRow = (item, idx) => {
            const tr = document.createElement('tr');
            tr.className = 'c-presupuesto-table__row';
            const subtotal = calcSubtotal(item);
            const grossPrice = grossFromNet(item.sale_price, item.vat);

            tr.innerHTML = `
                <td><input type="text" class="js-pl-field c-presupuesto-table__input" data-field="name_snapshot" data-idx="${idx}" value="${escapeHtml(item.name_snapshot)}" placeholder="Nombre"></td>
                <td><input type="text" class="js-pl-field c-presupuesto-table__input" data-field="description" data-idx="${idx}" value="${escapeHtml(item.description)}" placeholder="Descripción"></td>
                <td><input type="number" step="any" min="0.01" class="js-pl-field c-presupuesto-table__input c-presupuesto-table__input--qty" data-field="quantity" data-idx="${idx}" value="${item.quantity ?? 1}"></td>
                <td><select class="js-pl-field c-presupuesto-table__input" data-field="vat" data-idx="${idx}">${optionsHtml(vatChoices, item.vat)}</select></td>
                <td><input type="number" step="0.01" min="0" class="js-pl-price-net c-presupuesto-table__input c-presupuesto-table__input--price" data-idx="${idx}" value="${round2(item.sale_price)}"></td>
                <td><input type="number" step="0.01" min="0" class="js-pl-price-gross c-presupuesto-table__input c-presupuesto-table__input--price" data-idx="${idx}" value="${grossPrice}"></td>
                <td class="c-presupuesto-table__subtotal js-pl-subtotal" data-idx="${idx}">${fmtEUR(subtotal.total)} €</td>
                <td><button type="button" class="c-presupuesto-table__remove js-presupuesto-line-remove" data-idx="${idx}" aria-label="Eliminar línea">×</button></td>
            `;
            return tr;
        };

        const renderList = (items) => {
            tbody.innerHTML = '';
            if (!items.length) {
                const tr = document.createElement('tr');
                tr.className = 'c-presupuesto-table__empty-row';
                tr.innerHTML = `<td colspan="8">Todavía no hay ninguna línea. Busca un artículo/servicio arriba o añade una fila personalizada.</td>`;
                tbody.appendChild(tr);
                return;
            }
            items.forEach((item, idx) => tbody.appendChild(renderRow(item, idx)));
        };

        renderList(getItems());
        updateSummary(getItems());

        const addLine = (line) => {
            const items = getItems();
            items.push(line);
            syncHidden(items);
            renderList(items);
        };

        const blankLine = () => ({
            article_id: null,
            name_snapshot: '',
            sku_snapshot: '',
            description: '',
            quantity: 1,
            sale_price: 0,
            vat: 21,
        });

        // ── "+ Añadir fila": fila en blanco, todo se escribe a mano ────────
        addBlankBtn?.addEventListener('click', () => {
            addLine(blankLine());
            const lastNameInput = tbody.querySelector('tr:last-child [data-field="name_snapshot"]');
            lastNameInput?.focus();
        });

        // ── Buscador combinado: Artículos + Servicios a la vez ─────────────
        let debounceId = null;
        let lastResults = [];

        const fetchJson = (action, nonce, term) => {
            const formData = new FormData();
            formData.append('action', action);
            formData.append('nonce', nonce);
            formData.append('term', term);
            return fetch(av_data.av_ajax_url, { method: 'POST', body: formData })
                .then(r => r.json())
                .catch(() => null);
        };

        searchInput?.addEventListener('input', () => {
            const term = searchInput.value.trim();
            if (debounceId) clearTimeout(debounceId);

            if (term.length < 2) {
                resultsBox.innerHTML = '';
                resultsBox.classList.add('is-hidden');
                return;
            }

            debounceId = setTimeout(() => {
                Promise.all([
                    fetchJson('av_ajax_search_articulos_picker', av_data.nonce_articulos_picker, term),
                    fetchJson('av_ajax_search_servicios_picker', av_data.nonce_servicios_picker, term),
                ]).then(([artRes, servRes]) => {
                    const articulos = (artRes && artRes.data) || [];
                    const servicios = (servRes && servRes.data) || [];

                    lastResults = [
                        ...articulos.map(a => ({ ...a, _type: 'articulo' })),
                        ...servicios.map(s => ({ ...s, _type: 'servicio' })),
                    ];

                    resultsBox.classList.remove('is-hidden');

                    if (!lastResults.length) {
                        resultsBox.innerHTML = '<div class="c-sat-client-picker__no-results">No se ha encontrado ningún artículo ni servicio.</div>';
                        return;
                    }

                    resultsBox.innerHTML = lastResults.map((r, idx) => {
                        const isArticulo = r._type === 'articulo';
                        const price = isArticulo ? (r.unit_price || 0) : (r.price || 0);
                        const metaParts = isArticulo
                            ? [r.sku, r.category, fmtEUR(price) + ' €']
                            : [fmtEUR(price) + ' €'];
                        return `
                        <button type="button" class="c-sat-client-picker__result js-presupuesto-line-result" data-idx="${idx}">
                            <span class="c-sat-client-picker__result-name"><span class="c-presupuesto-table__result-type c-presupuesto-table__result-type--${r._type}">${isArticulo ? 'Artículo' : 'Servicio'}</span>${escapeHtml(r.name)}</span>
                            <span class="c-sat-client-picker__result-meta">${escapeHtml(metaParts.filter(Boolean).join(' · '))}</span>
                        </button>
                    `;
                    }).join('');
                });
            }, 300);
        });

        resultsBox?.addEventListener('click', (e) => {
            const btn = e.target.closest('.js-presupuesto-line-result');
            if (!btn) return;
            const r = lastResults[parseInt(btn.dataset.idx, 10)];
            if (!r) return;

            if (r._type === 'articulo') {
                addLine({
                    article_id: r.id,
                    name_snapshot: r.name,
                    sku_snapshot: r.sku,
                    description: buildArticuloDescription(r),
                    quantity: 1,
                    sale_price: r.unit_price || 0,
                    vat: r.vat || 21,
                });
            } else {
                // Un servicio no tiene SKU ni IVA propios en su catálogo: el
                // precio guardado en Servicios ya incluye IVA (misma
                // convención que "Reparación" en el SAT), así que se
                // convierte a precio sin IVA con el 21% por defecto.
                const vat = 21;
                const net = Math.round(((r.price || 0) / (1 + vat / 100)) * 100) / 100;
                addLine({
                    article_id: null,
                    name_snapshot: r.name,
                    sku_snapshot: '',
                    description: r.description || '',
                    quantity: 1,
                    sale_price: net,
                    vat,
                });
            }

            searchInput.value = '';
            resultsBox.innerHTML = '';
            resultsBox.classList.add('is-hidden');
        });

        // ── Eliminar línea (con "Deshacer") ─────────────────────────────────
        tbody.addEventListener('click', (e) => {
            const btn = e.target.closest('.js-presupuesto-line-remove');
            if (!btn) return;
            const idx = parseInt(btn.dataset.idx, 10);
            const items = getItems();
            const removed = items[idx];
            items.splice(idx, 1);
            syncHidden(items);
            renderList(items);

            if (removed) {
                av_show_undo_row(tbody, idx, 8, removed.name_snapshot, () => {
                    const current = getItems();
                    current.splice(idx, 0, removed);
                    syncHidden(current);
                    renderList(current);
                });
            }
        });

        // ── Edición en vivo de cualquier celda ─────────────────────────────
        // Repinta SOLO el texto de subtotal y el otro campo de precio (no el
        // input que se está escribiendo), para no perder el foco ni el
        // cursor mientras se escribe en una fila.
        const refreshRowDisplay = (idx, items) => {
            const item = items[idx];
            if (!item) return;
            const subtotal = calcSubtotal(item);
            const grossPrice = grossFromNet(item.sale_price, item.vat);

            const netInput   = tbody.querySelector(`.js-pl-price-net[data-idx="${idx}"]`);
            const grossInput = tbody.querySelector(`.js-pl-price-gross[data-idx="${idx}"]`);
            if (netInput   && document.activeElement !== netInput)   netInput.value = round2(item.sale_price);
            if (grossInput && document.activeElement !== grossInput) grossInput.value = grossPrice;

            const subtotalEl = tbody.querySelector(`.js-pl-subtotal[data-idx="${idx}"]`);
            if (subtotalEl) subtotalEl.textContent = `${fmtEUR(subtotal.total)} €`;
        };

        tbody.addEventListener('input', (e) => {
            const target = e.target;
            const idx = parseInt(target.dataset.idx, 10);
            const items = getItems();
            if (!items[idx]) return;

            if (target.classList.contains('js-pl-price-net')) {
                items[idx].sale_price = target.value === '' ? 0 : parseFloat(String(target.value).replace(',', '.'));
            } else if (target.classList.contains('js-pl-price-gross')) {
                const gross = target.value === '' ? 0 : parseFloat(String(target.value).replace(',', '.'));
                items[idx].sale_price = netFromGross(gross, items[idx].vat);
            } else if (target.classList.contains('js-pl-field')) {
                const key = target.dataset.field;
                if (key === 'quantity' || key === 'vat') {
                    items[idx][key] = target.value === '' ? 0 : parseFloat(String(target.value).replace(',', '.'));
                } else {
                    items[idx][key] = target.value;
                }
            } else {
                return;
            }

            syncHidden(items);
            refreshRowDisplay(idx, items);
        });

        // ── Validación mínima en el frontend (el backend también la aplica) ──
        const form = widget.closest('form');
        form?.addEventListener('submit', (e) => {
            if (getItems().length === 0) {
                e.preventDefault();
                errorMsg?.classList.remove('is-hidden');
                errorMsg?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    };

    const av_presupuestos_filter_async = () => {
        av_init_async_filter({
            formSelector: '.js-presupuestos-filter-form',
            countId: 'presupuestos-count',
            listId: 'presupuestos-list',
            clearFiltersId: 'presupuestos-clear-filters',
            loaderSelector: '.js-presupuestos-loader',
            submitSelector: '.js-presupuestos-filter-submit',
            ajaxAction: 'av_ajax_filter_presupuestos',
            includeUrlFilterParam: false
        })
    }

    // Comparte la misma regla entre "marcar como reparado" y "finalizar":
    // basta con que Reparación O Piezas pedidas tenga contenido (no hace
    // falta rellenar las dos), y si no hay precio en ningún sitio (ni Coste
    // Final manual ni ninguna línea) se pregunta explícitamente si se quiere
    // continuar a 0€ en vez de bloquear sin más. Devuelve { ok, hasPositivePrice }:
    // ok=false si hay que bloquear el envío (ya se ha mostrado el alert/
    // confirm oportuno); hasPositivePrice indica si el precio resuelto es
    // mayor que 0 (manual o de alguna línea) — a 0€ (confirmado o puesto a
    // mano) no hace falta pedir tipo de pago.
    const av_sat_form_check_repair_and_price = (formulario, accion) => {

        // Los widgets de Reparación y Piezas pedidas comparten las mismas
        // clases (.js-repair-hidden, etc.); solo el "name" del hidden los
        // distingue.
        const parseItems = (hidden) => {
            if (!hidden) return [];
            try {
                const items = JSON.parse(hidden.value);
                return Array.isArray(items) ? items : [];
            } catch (e) {
                return hidden.value.trim() ? [{ text: hidden.value.trim(), price: '' }] : [];
            }
        };

        // "Artículos y servicios" (tabla nueva, ver components/c-sat-lines.php):
        // cada línea guarda sale_price (sin IVA)/quantity/vat, no un "price"
        // plano como los antiguos widgets — se calcula el importe con IVA
        // para poder comparar todo con el mismo criterio que antes.
        const linesHidden     = formulario.querySelector('[name="sat-lines"]');
        const articulosHidden = formulario.querySelector('[name="articulos-usados"]');
        const linesItems      = parseItems(linesHidden).map(it => {
            const net = parseFloat(it.sale_price) || 0;
            const qty = parseFloat(it.quantity) || 0;
            const vat = parseFloat(it.vat) || 0;
            return { price: Math.round(net * qty * (1 + vat / 100) * 100) / 100 };
        });
        // Los artículos reales del catálogo (con quantity/subtotal, no price)
        // también cuentan como "trabajo hecho" para esta validación: un
        // técnico que solo usa piezas de inventario, sin texto libre, tiene
        // que poder finalizar igual.
        const articulosItems  = parseItems(articulosHidden).map(it => ({ price: it.subtotal }));

        if (linesItems.length === 0 && articulosItems.length === 0) {
            alert(`Para ${accion} el SAT debes añadir algún artículo, servicio o artículo utilizado.`);
            const searchInput = formulario.querySelector('.js-sat-lines-search');
            if (searchInput) searchInput.focus();
            return { ok: false, hasPositivePrice: false };
        }

        const priceInput  = formulario.querySelector('[name="price"]');
        const manualPrice = (priceInput?.value || '').trim();
        const manualValue = parseFloat(manualPrice.replace(',', '.'));

        const itemsHavePrice = [...linesItems, ...articulosItems].some(it => {
            const p = parseFloat(String(it.price || '').replace(',', '.'));
            return !isNaN(p) && p > 0;
        });

        if (manualPrice === '' && !itemsHavePrice) {
            const confirmarSinCoste = confirm(`No se ha indicado ningún precio (ni en Coste Final ni en las líneas de artículos/servicios). ¿Quieres ${accion} este SAT con precio 0€?`);
            if (!confirmarSinCoste) {
                priceInput?.focus();
                return { ok: false, hasPositivePrice: false };
            }
            if (priceInput) priceInput.value = '0';
            return { ok: true, hasPositivePrice: false };
        }

        return { ok: true, hasPositivePrice: itemsHavePrice || (!isNaN(manualValue) && manualValue > 0) };
    };

    // Envío pendiente de reintentar en cuanto se elija una garantía en la
    // modal (guarda qué formulario y qué botón lo disparó, para reproducir
    // exactamente el mismo submit — Guardar o Finalizar SAT — al confirmar).
    let av_warranty_modal_pending = null;

    // Abre la modal de "elige la garantía" en vez de un simple alert(); si no
    // hay modal en la página, o no se sabe con certeza qué botón disparó el
    // envío bloqueado, devuelve false para que quien llama use el alert() de
    // siempre como último recurso. IMPORTANTE: sin un submitter real y
    // perteneciente a este formulario, NO se debe reintentar el envío luego
    // (un reintento "genérico" del formulario tomaría como botón por defecto
    // el PRIMER submit del DOM — "Finalizar SAT" va antes que "Guardar" — y
    // finalizaría el SAT aunque el técnico solo quisiera guardar).
    const av_sat_form_warranty_modal_open = (formulario, submitter) => {
        const modal = document.querySelector('.js-sat-form__warranty-modal');
        if (!modal) return false;
        if (!submitter || submitter.form !== formulario || submitter.type !== 'submit') return false;

        av_warranty_modal_pending = { formulario, submitter };

        const modalSelect = modal.querySelector('.js-sat-form__warranty-modal-select');
        if (modalSelect) modalSelect.value = '';

        modal.classList.add('is-active');
        document.body.classList.add('is-overflow-hidden');
        modalSelect?.focus();
        return true;
    };

    const av_sat_form_warranty_modal = () => {

        const modal = document.querySelector('.js-sat-form__warranty-modal');
        if (!modal) return;

        const modalSelect  = modal.querySelector('.js-sat-form__warranty-modal-select');
        const confirmBtn   = modal.querySelector('.js-sat-form__warranty-modal-confirm');

        const closeModal = () => {
            modal.classList.remove('is-active');
            document.body.classList.remove('is-overflow-hidden');
            av_warranty_modal_pending = null;
        };

        modal.querySelectorAll('.js-sat-form__warranty-modal-close').forEach(el => {
            el.addEventListener('click', closeModal);
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && modal.classList.contains('is-active')) closeModal();
        });

        confirmBtn?.addEventListener('click', () => {
            if (!modalSelect || !modalSelect.value || !av_warranty_modal_pending) return;

            const { formulario, submitter } = av_warranty_modal_pending;
            const realSelect = formulario.querySelector('[name="warranty-period"]');
            if (realSelect) {
                realSelect.value = modalSelect.value;
                realSelect.dispatchEvent(new Event('change', { bubbles: true }));
            }

            closeModal();
            // Vuelve a pulsar EXACTAMENTE el mismo botón que se pulsó antes
            // (Guardar o Finalizar SAT) ahora que la garantía ya está puesta,
            // para que pase el resto de validaciones con normalidad. A
            // propósito NO se usa formulario.requestSubmit(submitter): si el
            // submitter dejara de ser válido por lo que sea, requestSubmit
            // recurriría en silencio al primer botón submit del formulario
            // ("Finalizar SAT" va antes que "Guardar" en el DOM), pudiendo
            // finalizar el SAT sin querer. submitter.click() reproduce el
            // clic real sobre ESE botón concreto, sin ambigüedad posible.
            submitter.click();
        });
    };

    const av_sat_form_validate_reparado = () => {

        const formulario = document.querySelector('.c-sat-form__form');
        if (!formulario) return;

        formulario.addEventListener('submit', (e) => {

            // Esta validación es del guardado normal ("Guardar"): al finalizar
            // (botón "Finalizar SAT") no hace falta reparación/artículos ni
            // precio, ver av_sat_form_validate_finalizar() más abajo.
            if (e.submitter?.name === 'sat-action' && e.submitter?.value === 'finalizar') return;

            const estadoSelect = formulario.querySelector('[name="estado"]');
            if (!estadoSelect || estadoSelect.value !== 'reparado') return;

            // Garantía de la reparación obligatoria al guardar como "reparado"
            // (un SAT que ya es en sí mismo una garantía no la necesita: ese
            // campo ni se muestra, is-warranty se encarga aparte).
            const isWarrantyInput = formulario.querySelector('[name="is-warranty"]');
            const isWarranty      = isWarrantyInput && isWarrantyInput.value === '1';
            const warrantySelect  = formulario.querySelector('[name="warranty-period"]');
            if (!isWarranty && warrantySelect && !warrantySelect.value) {
                e.preventDefault();
                if (!av_sat_form_warranty_modal_open(formulario, e.submitter)) {
                    alert('Antes de guardar el SAT como reparado debes indicar la garantía de la reparación.');
                    warrantySelect.focus();
                }
                return;
            }

            if (!av_sat_form_check_repair_and_price(formulario, 'marcar como reparado').ok) {
                e.preventDefault();
            }
        });
    }

    // Habilita el botón "Finalizar SAT" solo cuando el estado elegido es uno
    // de los que se pueden cerrar (reparado/no reparado/garantía). El propio
    // botón sigue estando siempre disponible al recargar según el valor
    // guardado (ver disabled en c-sat-form.php); esto solo sincroniza el
    // disabled en vivo si el técnico cambia el desplegable sin recargar.
    const av_sat_form_finalize_button_toggle = () => {

        const formulario  = document.querySelector('.c-sat-form__form');
        const finalizeBtn = formulario?.querySelector('.js-sat-form__finalize-btn');
        const estadoSelect = formulario?.querySelector('[name="estado"]');
        if (!formulario || !finalizeBtn || !estadoSelect) return;

        const finalizables = ['reparado', 'no-reparado', 'garantia'];

        const sync = () => {
            const ok = finalizables.includes(estadoSelect.value);
            finalizeBtn.disabled = !ok;
            finalizeBtn.title = ok
                ? 'Cierra este SAT y lo mueve a Finalizados'
                : 'Solo se puede finalizar un SAT marcado como reparado, no reparado o en garantía';
        };

        sync();
        estadoSelect.addEventListener('change', sync);
    }

    // Valida (y completa, con el motivo/banner) el cierre real del SAT al
    // pulsar "Finalizar SAT" — distinto de "Guardar", que nunca finaliza.
    // A diferencia de marcar "reparado" en un guardado normal, aquí NO hace
    // falta ninguna línea de reparación/artículos: se puede finalizar con
    // solo el Coste Final relleno (o sin nada, si no es "reparado").
    const av_sat_form_validate_finalizar = () => {

        const formulario = document.querySelector('.c-sat-form__form');
        if (!formulario) return;

        formulario.addEventListener('submit', (e) => {

            if (!(e.submitter?.name === 'sat-action' && e.submitter?.value === 'finalizar')) return;

            const estadoSelect = formulario.querySelector('[name="estado"]');
            const estado = estadoSelect?.value;
            if (!['reparado', 'no-reparado', 'garantia'].includes(estado)) {
                e.preventDefault();
                alert('Solo se puede finalizar un SAT marcado como reparado, no reparado o en garantía.');
                return;
            }

            // Un SAT de garantía se cierra siempre como "garantía", sin coste,
            // pase lo que pase con el desplegable (misma regla de siempre,
            // solo que ahora se aplica al finalizar en vez de en cualquier
            // guardado).
            const isWarrantyInput = formulario.querySelector('[name="is-warranty"]');
            const isWarranty      = isWarrantyInput && isWarrantyInput.value === '1';
            const estadoFinal     = isWarranty ? 'garantia' : estado;

            const reasonWrapper = formulario.querySelector('.js-sat-form__unpaid-reason-wrapper');
            const reasonInput   = formulario.querySelector('[name="unpaid-reason"]');

            // Garantía (propia o forzada por is-warranty): nunca pide precio,
            // pago, periodo de garantía ni motivo.
            if (estadoFinal === 'garantia') {
                reasonWrapper?.classList.add('is-hidden');
                if (reasonInput) reasonInput.required = false;
                return;
            }

            const priceInput  = formulario.querySelector('[name="price"]');
            const priceValue  = parseFloat((priceInput?.value || '').replace(',', '.'));
            const hasPrice    = !isNaN(priceValue) && priceValue > 0;

            if (estadoFinal === 'reparado') {
                // Garantía de la reparación obligatoria (no aplica a "no
                // reparado": no hay nada que reparar ni que garantizar).
                const warrantySelect = formulario.querySelector('[name="warranty-period"]');
                if (warrantySelect && !warrantySelect.value) {
                    e.preventDefault();
                    if (!av_sat_form_warranty_modal_open(formulario, e.submitter)) {
                        alert('Antes de finalizar el SAT debes indicar la garantía de la reparación.');
                        warrantySelect.focus();
                    }
                    return;
                }
                if (!hasPrice) {
                    e.preventDefault();
                    alert('Para finalizar un SAT como reparado debes indicar el precio.');
                    priceInput?.focus();
                    return;
                }
                reasonWrapper?.classList.add('is-hidden');
                if (reasonInput) reasonInput.required = false;
            } else {
                // no-reparado: sin cobrar nada hace falta justificar el motivo.
                if (!hasPrice) {
                    reasonWrapper?.classList.remove('is-hidden');
                    if (reasonInput) reasonInput.required = true;
                    if (!reasonInput || !reasonInput.value.trim()) {
                        e.preventDefault();
                        reasonInput?.focus();
                        return;
                    }
                } else {
                    reasonWrapper?.classList.add('is-hidden');
                    if (reasonInput) reasonInput.required = false;
                }
            }

            // Con precio (reparado siempre, o no-reparado con algún cobro
            // puntual) hace falta indicar cómo se ha cobrado.
            if (hasPrice) {
                const paymentSelect = formulario.querySelector('[name="price-description"]');
                if (!paymentSelect || !paymentSelect.value) {
                    e.preventDefault();
                    alert('Antes de finalizar el SAT debes indicar el tipo de pago.');
                    paymentSelect?.focus();
                    return;
                }
            }
        });
    }

    // Anticipo (paga y señal): la forma de pago del anticipo solo se muestra
    // y se exige cuando hay una cantidad de anticipo indicada.
    const av_sat_form__anticipo_toggle = () => {

        const anticipoInput = document.querySelector('.js-sat-form__anticipo');
        const wrapper        = document.querySelector('.js-sat-form__anticipo-payment-wrapper');
        const paymentSelect  = document.querySelector('.js-sat-form__anticipo-payment');
        if (!anticipoInput || !wrapper || !paymentSelect) return;

        const sync = () => {
            const value = parseFloat((anticipoInput.value || '').replace(',', '.'));
            const hasAnticipo = !isNaN(value) && value > 0;

            wrapper.classList.toggle('is-hidden', !hasAnticipo);
            paymentSelect.required = hasAnticipo;

            if (!hasAnticipo) paymentSelect.value = '';
        };

        anticipoInput.addEventListener('input', sync);
        sync();
    }

    const av_sat_form_validate_anticipo = () => {

        const formulario = document.querySelector('.c-sat-form__form');
        if (!formulario) return;

        formulario.addEventListener('submit', (e) => {

            const anticipoInput = formulario.querySelector('[name="anticipo"]');
            if (!anticipoInput) return;

            const anticipoValue = parseFloat((anticipoInput.value || '').replace(',', '.'));
            if (isNaN(anticipoValue) || anticipoValue <= 0) return;

            const paymentSelect = formulario.querySelector('[name="anticipo-payment"]');
            if (!paymentSelect || !paymentSelect.value) {
                e.preventDefault();
                alert('Has indicado un anticipo: selecciona su forma de pago.');
                paymentSelect?.focus();
            }
        });
    }

    // Modal para ver una foto en grande. Se crea una sola vez y la comparten
    // todos los enlaces .js-photo-zoom (fotos del SAT).
    const av_photo_modal = () => {

        // Los listeners son delegados: basta con montarla una vez por página
        if (window.__av_photo_modal_ready) return;
        window.__av_photo_modal_ready = true;

        const modal = document.createElement('div');
        modal.className = 'c-photo-modal js-photo-modal';
        modal.innerHTML =
            '<button type="button" class="c-photo-modal__close js-photo-modal-close" aria-label="Cerrar">' +
                '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>' +
            '</button>' +
            '<button type="button" class="c-photo-modal__nav c-photo-modal__nav--prev js-photo-modal-prev" aria-label="Anterior">' +
                '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>' +
            '</button>' +
            '<img class="c-photo-modal__img js-photo-modal-img" src="" alt="">' +
            '<button type="button" class="c-photo-modal__nav c-photo-modal__nav--next js-photo-modal-next" aria-label="Siguiente">' +
                '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>' +
            '</button>' +
            '<span class="c-photo-modal__counter js-photo-modal-counter"></span>';
        document.body.appendChild(modal);

        const modalImg = modal.querySelector('.js-photo-modal-img');
        const counter  = modal.querySelector('.js-photo-modal-counter');

        let photos = [];
        let index  = 0;

        const render = () => {
            const photo = photos[index];
            if (!photo) return;

            modalImg.src = photo.src;
            modalImg.alt = photo.alt || '';
            counter.textContent = photos.length > 1 ? (index + 1) + ' / ' + photos.length : '';
            modal.classList.toggle('has-slider', photos.length > 1);
        };

        const closeModal = () => {
            modal.classList.remove('is-active');
            modalImg.src = '';
            photos = [];
        };

        const move = (step) => {
            if (photos.length < 2) return;
            index = (index + step + photos.length) % photos.length;
            render();
        };

        // Delegación: sirve también para los enlaces que se crean después
        document.addEventListener('click', (e) => {
            const zoom = e.target.closest('.js-photo-zoom');
            if (!zoom) return;

            e.preventDefault();

            const href = zoom.getAttribute('href');
            if (!href || href === '#') return;

            // Si el enlace está dentro de una galería, la modal las recorre todas
            const gallery = zoom.closest('.js-photo-gallery');
            const links   = gallery ? Array.from(gallery.querySelectorAll('.js-photo-zoom')) : [zoom];

            photos = links.map(link => {
                const img = link.querySelector('img');
                return { src: link.getAttribute('href'), alt: img ? img.alt : '' };
            });
            index = Math.max(0, links.indexOf(zoom));

            render();
            modal.classList.add('is-active');
        });

        // Cerrar: fondo, botón o Escape. Flechas para pasar fotos.
        modal.addEventListener('click', (e) => {
            if (e.target.closest('.js-photo-modal-prev')) return move(-1);
            if (e.target.closest('.js-photo-modal-next')) return move(1);
            if (e.target === modal || e.target.closest('.js-photo-modal-close')) closeModal();
        });

        document.addEventListener('keydown', (e) => {
            if (!modal.classList.contains('is-active')) return;

            if (e.key === 'Escape')     closeModal();
            if (e.key === 'ArrowLeft')  move(-1);
            if (e.key === 'ArrowRight') move(1);
        });
    };

    // Campos de foto del SAT (estado del dispositivo, precinto de garantía…):
    // botón "Tomar foto" que abre la cámara del móvil y previsualización de la
    // imagen elegida antes de guardar.
    const av_sat_photo_fields = () => {

        av_photo_modal();

        document.querySelectorAll('.js-photo-field').forEach(field => {

            const input       = field.querySelector('.js-photo-input');
            const cameraBtn   = field.querySelector('.js-photo-camera');
            const preview     = field.querySelector('.js-photo-preview');
            const previewList = preview?.querySelector('.js-photo-preview-list');
            const previewLink = preview?.querySelector('.js-photo-preview-link');

            if (!input) return;

            const maxFiles = parseInt(input.dataset.max || '1', 10);
            let   freeSlots = parseInt(input.dataset.left || '1', 10);

            // ── Marcar fotos guardadas para eliminar ────────────────────────
            const removeInput = field.querySelector('.js-photo-remove-input');
            const items       = Array.from(field.querySelectorAll('.js-photo-item'));
            const hintLeft    = field.querySelector('.js-photo-hint-left');

            const syncRemovals = (notify = true) => {
                const removed = items.filter(item => item.classList.contains('is-removed'));

                if (removeInput) {
                    removeInput.value = removed.length
                        ? JSON.stringify(removed.map(item => item.dataset.url))
                        : '';

                    // Cambiar el valor por JS no dispara eventos: hay que avisar al
                    // formulario para que se active el botón de guardar.
                    if (notify) {
                        removeInput.dispatchEvent(new Event('input',  { bubbles: true }));
                        removeInput.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                }

                // Al marcar fotos se liberan plazas para subir otras en el mismo guardado
                freeSlots = maxFiles - (items.length - removed.length);
                if (maxFiles > 1) {
                    input.disabled = freeSlots < 1;
                    if (hintLeft) {
                        hintLeft.textContent = freeSlots > 0
                            ? 'Puedes añadir hasta ' + maxFiles + ' fotos (' + freeSlots + ' disponibles).'
                            : 'Has llegado al máximo de ' + maxFiles + ' fotos.';
                    }
                }
            };

            items.forEach(item => {
                const removeBtn = item.querySelector('.js-photo-remove');
                if (!removeBtn) return;

                removeBtn.addEventListener('click', () => {
                    const removed = item.classList.toggle('is-removed');
                    removeBtn.title = removed ? 'Recuperar esta foto' : 'Eliminar esta foto';
                    syncRemovals();
                });
            });

            // "Cancelar" del formulario: se desmarcan las fotos marcadas
            removeInput?.addEventListener('av:photos-reset', () => {
                items.forEach(item => {
                    item.classList.remove('is-removed');
                    const btn = item.querySelector('.js-photo-remove');
                    if (btn) btn.title = 'Eliminar esta foto';
                });
                input.value = '';
                if (preview) preview.classList.add('is-hidden');
                clearBlobs();
                syncRemovals(false);
            });

            // URLs temporales de las fotos elegidas, para liberarlas al cambiar
            let blobUrls = [];

            const clearBlobs = () => {
                blobUrls.forEach(url => URL.revokeObjectURL(url));
                blobUrls = [];
            };

            if (cameraBtn) {
                // El botón se muestra por CSS en pantallas táctiles; esto cubre los
                // navegadores móviles que no responden bien a (pointer: coarse).
                if (navigator.maxTouchPoints > 0 || 'ontouchstart' in window) {
                    cameraBtn.classList.add('is-touch');
                }

                cameraBtn.addEventListener('click', () => {
                    // capture solo mientras se pulsa este botón: así el selector normal
                    // sigue dejando elegir una foto ya existente.
                    input.setAttribute('capture', 'environment');
                    input.click();
                });
            }

            input.addEventListener('change', () => {
                input.removeAttribute('capture');

                const files = Array.from(input.files || []);
                if (!files.length || !preview) return;

                // No se pueden superar las plazas libres del campo
                if (maxFiles > 1 && files.length > freeSlots) {
                    alert('Solo puedes añadir ' + freeSlots + (freeSlots === 1 ? ' foto más' : ' fotos más') +
                          ' (máximo ' + maxFiles + ').');
                    input.value = '';
                    clearBlobs();
                    preview.classList.add('is-hidden');
                    return;
                }

                clearBlobs();

                // Las miniaturas/el enlace apuntan a los archivos recién elegidos,
                // que todavía no están subidos.
                if (previewList) {
                    previewList.innerHTML = '';
                    files.forEach((file, i) => {
                        const url = URL.createObjectURL(file);
                        blobUrls.push(url);

                        const link = document.createElement('a');
                        link.className = 'js-photo-zoom';
                        link.href = url;
                        link.title = 'Ver la foto en grande';
                        link.innerHTML = '<img src="' + url + '" alt="Foto seleccionada ' + (i + 1) + '">';
                        previewList.appendChild(link);
                    });
                } else if (previewLink) {
                    const url = URL.createObjectURL(files[0]);
                    blobUrls.push(url);
                    previewLink.setAttribute('href', url);
                }

                preview.classList.remove('is-hidden');

                // Algunos navegadores móviles no lanzan "input" al elegir archivo:
                // sin esto el botón de guardar se quedaría desactivado.
                input.dispatchEvent(new Event('input', { bubbles: true }));
            });

        });
    };

    const av_sat_form_signature_pad = () => {
        const wrapper = document.querySelector('.js-sat-form__signature-pad');
        const canvas = wrapper?.querySelector('canvas');
        const clearBtn = wrapper?.querySelector('.js-signature-clear');
        const saveBtn = document.querySelector('.js-signature-save');
        const acceptanceCheckbox = document.querySelector('#signature-confirmed');
        const satId = document.querySelector('#sat-id').value;       

        if (!canvas) return;
        
        const signaturePad = new SignaturePad(canvas);                
        
        function resizeCanvas() {
            const ratio = Math.max(window.devicePixelRatio || 1, 1);

            const width = wrapper.offsetWidth;
            const height = wrapper.offsetHeight;

            canvas.width = width * ratio;
            canvas.height = height * ratio;

            const ctx = canvas.getContext('2d');
            ctx.setTransform(ratio, 0, 0, ratio, 0, 0);

            // signaturePad.clear();
        }

        // window.addEventListener('resize', resizeCanvas);
        resizeCanvas();

        clearBtn.addEventListener('click', () => {
            signaturePad.clear();
        });

        saveBtn.addEventListener('click', () => {

            const imageData = signaturePad.toDataURL('image/png');

            if (signaturePad.isEmpty()) {
                alert('Por favor, firma antes de guardar.');
                return;
            }
            if(!acceptanceCheckbox.checked){
                alert('Por favor, acepta la firma antes de guardar.');
                return;
            }

            const saveBlock = document.querySelector('.b-save')
            const nodeSignature = document.querySelector('.c-sat-form__wrapper-box--signature')

            const formData = new FormData();
            formData.append('action', 'av_ajax_save_signature');
            formData.append('image', imageData);
            formData.append('sat-id', satId);

            fetch(av_data.av_ajax_url, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(results => {
                if(results.success){
                    saveBlock.classList.add('is-active')
                    nodeSignature.classList.add('no-click')

                    timeoutId = setTimeout(()=>{
                        saveBlock.classList.remove('is-active')                        
                    }, 3000)
                }else{
                    alert('Error al guardar la firma. Por favor, inténtalo de nuevo.')
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        })
    };

    const av_remove_search_sat = () => {

        const node = document.querySelector('.js-remove-search-list-sats')

        node.addEventListener('click', () => {
            // 1️⃣ Cogemos la URL sin parámetros
            const urlBase = window.location.origin + window.location.pathname;

            // 2️⃣ Recargamos la página limpia
            window.location.href = urlBase;
        })

    }

    // Buscador asincrono generico: sirve tanto para el listado de SATs como
    // para el de clientes (y cualquier otro listado que siga el mismo
    // patron de marcado). "config" indica el formulario, los contenedores a
    // sustituir, la accion AJAX a llamar y (opcionalmente) que hacer despues
    // de recibir el resultado.
    const av_init_async_filter = (config) => {

        const form = document.querySelector(config.formSelector)
        if (!form) return

        const countEl        = document.getElementById(config.countId)
        const listEl         = document.getElementById(config.listId)
        const loaderEl       = document.querySelector(config.loaderSelector)
        const submitBtn      = form.querySelector(config.submitSelector)
        const clearFiltersEl = document.getElementById(config.clearFiltersId)
        const filterFields   = Array.from(form.querySelectorAll('.js-filter-field'))

        const setLoading = (isLoading) => {
            loaderEl.classList.toggle('is-active', isLoading)
            submitBtn.classList.toggle('is-loading', isLoading)
            submitBtn.disabled = isLoading
        }

        const filterCountEl = form.querySelector('.js-filter-count')

        const updateClearFiltersVisibility = () => {
            const active = filterFields.filter(field => field.value !== '').length

            if (clearFiltersEl) clearFiltersEl.classList.toggle('is-hidden', active === 0)

            // Contador de filtros aplicados en la cabecera del bloque
            if (filterCountEl) {
                filterCountEl.textContent = active
                filterCountEl.classList.toggle('is-hidden', active === 0)
            }
        }

        const updateFieldClearButton = (input) => {
            const btn = input.closest('.js-filter-control')?.querySelector('.js-field-clear')
            if (btn) btn.classList.toggle('is-visible', input.value !== '')
        }

        const runSearch = (paged) => {
            const formData = new FormData(form)

            const publicParams = new URLSearchParams()
            formData.forEach((value, key) => {
                if (key === 'nonce') return
                if (value !== '') publicParams.set(key, value)
            })

            // La pestaña activa (en curso / finalizados) solo se arrastra cuando no
            // hay ningún campo del buscador relleno: al buscar se busca en TODOS.
            if (config.includeUrlFilterParam && !filterFields.some(field => field.value !== '')) {
                const currentFilter = new URLSearchParams(window.location.search).get('filter') || ''
                if (currentFilter) publicParams.set('filter', currentFilter)
            }

            if (paged && paged > 1) publicParams.set('paged', paged)

            const ajaxParams = new URLSearchParams(publicParams)
            ajaxParams.set('action', config.ajaxAction)
            ajaxParams.set('nonce', formData.get('nonce'))

            setLoading(true)

            fetch(`${av_data.av_ajax_url}?${ajaxParams.toString()}`, {
                credentials: 'same-origin'
            })
                .then(res => res.json())
                .then(json => {
                    if (!json || !json.success) throw new Error(`${config.ajaxAction} request failed`)
                    countEl.innerHTML = json.data.count
                    listEl.innerHTML = json.data.list

                    if (config.onResultsUpdated) config.onResultsUpdated()

                    const query = publicParams.toString()
                    window.history.pushState({}, '', window.location.pathname + (query ? '?' + query : ''))

                    updateClearFiltersVisibility()
                    av_highlight_matches(form, listEl)
                })
                .catch(() => {
                    form.submit()
                })
                .finally(() => {
                    setLoading(false)
                })
        }

        form.addEventListener('submit', (e) => {
            e.preventDefault()
            runSearch(1)
        })

        // Cruz para borrar un campo individualmente: lo vacía y relanza la
        // búsqueda automáticamente, sin necesidad de pulsar "Buscar".
        filterFields.forEach(field => {
            updateFieldClearButton(field)
            field.addEventListener('input', () => updateFieldClearButton(field))
            field.addEventListener('change', () => updateFieldClearButton(field))
        })

        form.querySelectorAll('.js-field-clear').forEach(btn => {
            btn.addEventListener('click', () => {
                const input = btn.closest('.js-filter-control')?.querySelector('.js-filter-field')
                if (!input) return
                input.value = ''
                btn.classList.remove('is-visible')
                runSearch(1)
            })
        })

        updateClearFiltersVisibility()

        // Resultados de la carga inicial: los filtros vienen de la URL
        av_highlight_matches(form, listEl)
    }

    // Deja marcada la pestaña que corresponde a lo que se está viendo: al buscar
    // se listan todos los SATs, así que la pestaña activa pasa a ser "Todos".
    const av_sync_sats_tabs = () => {
        const form = document.querySelector('.js-sats-filter-form')
        if (!form) return

        const hasSearch = Array.from(form.querySelectorAll('.js-filter-field'))
            .some(field => field.value !== '')
        const urlFilter = new URLSearchParams(window.location.search).get('filter') || 'en-curso'
        const activeId  = hasSearch ? 'todos' : urlFilter

        document.querySelectorAll('.js-filter-all').forEach(item => {
            item.classList.toggle('c-list-cpt-sats__menu-item--active', item.dataset.id === activeId)
        })
    }

    // Resalta dentro de los resultados el texto buscado en los filtros de texto
    // y número, para ver de un vistazo por qué ha entrado cada fila.
    const av_highlight_matches = (form, container) => {

        if (!form || !container) return;

        // Quitar los resaltados anteriores
        container.querySelectorAll('mark.c-filters-mark').forEach(mark => {
            const parent = mark.parentNode;
            parent.replaceChild(document.createTextNode(mark.textContent), mark);
            parent.normalize();
        });

        // Los desplegables y las fechas no se resaltan: no son texto libre
        const tiposTexto = ['text', 'number', 'search', 'tel', 'email'];
        const terms = Array.from(form.querySelectorAll('.js-filter-field'))
            .filter(field => field.tagName !== 'SELECT' && tiposTexto.includes(field.type))
            .map(field => field.value.trim())
            .filter(value => value !== '');

        if (!terms.length) return;

        // Comparación sin tildes ni mayúsculas, manteniendo 1 carácter = 1 posición
        // para poder recortar después el texto original.
        const fold = (str) => Array.from(str).map(ch => (ch.normalize('NFD')[0] || ch).toLowerCase());
        const foldedTerms = terms.map(term => fold(term).join(''));

        const walker = document.createTreeWalker(container, NodeFilter.SHOW_TEXT, {
            acceptNode: (node) => {
                if (!node.nodeValue.trim()) return NodeFilter.FILTER_REJECT;
                const tag = node.parentNode ? node.parentNode.nodeName : '';
                if (['SCRIPT', 'STYLE', 'OPTION', 'SELECT', 'TEXTAREA', 'INPUT'].includes(tag)) {
                    return NodeFilter.FILTER_REJECT;
                }
                return NodeFilter.FILTER_ACCEPT;
            }
        });

        const nodes = [];
        while (walker.nextNode()) nodes.push(walker.currentNode);

        nodes.forEach(node => {

            const chars  = Array.from(node.nodeValue);
            const folded = fold(node.nodeValue).join('');

            const ranges = [];
            foldedTerms.forEach(term => {
                let from = 0;
                let i;
                while ((i = folded.indexOf(term, from)) !== -1) {
                    ranges.push([i, i + term.length]);
                    from = i + term.length;
                }
            });

            if (!ranges.length) return;

            // Une los tramos que se solapan (dos filtros que coinciden en lo mismo)
            ranges.sort((a, b) => a[0] - b[0]);
            const merged = [];
            ranges.forEach(range => {
                const last = merged[merged.length - 1];
                if (last && range[0] <= last[1]) {
                    last[1] = Math.max(last[1], range[1]);
                } else {
                    merged.push(range.slice());
                }
            });

            const frag = document.createDocumentFragment();
            let pos = 0;

            merged.forEach(([ini, fin]) => {
                if (ini > pos) frag.appendChild(document.createTextNode(chars.slice(pos, ini).join('')));
                const mark = document.createElement('mark');
                mark.className = 'c-filters-mark';
                mark.textContent = chars.slice(ini, fin).join('');
                frag.appendChild(mark);
                pos = fin;
            });

            if (pos < chars.length) frag.appendChild(document.createTextNode(chars.slice(pos).join('')));

            node.parentNode.replaceChild(frag, node);
        });
    };

    // Botón "Filtros": despliega los campos que no están siempre a la vista.
    // Si se llega a la página con alguno aplicado, el panel ya viene abierto.
    const av_filters_toggle = () => {

        document.querySelectorAll('.js-filters-toggle').forEach(btn => {

            const form  = btn.closest('form');
            const panel = form?.querySelector('.js-filters-more');
            if (!panel) return;

            btn.addEventListener('click', () => {
                const open = panel.classList.toggle('is-open');
                btn.classList.toggle('is-open', open);
                btn.setAttribute('aria-expanded', open ? 'true' : 'false');
            });

        });
    };

    // Modal de alta/edición de técnicos: rellena el formulario con los datos
    // de la fila pulsada y muestra/oculta el modal. El envío sigue siendo un
    // POST normal a admin-post.php (no AJAX), así que al guardar se recarga
    // la página con el listado ya actualizado.
    const av_usuarios_modal = () => {

        const modal = document.querySelector('.js-usuarios-modal');
        if (!modal) return;

        const form         = modal.querySelector('.js-usuarios-form');
        const title        = modal.querySelector('.js-usuarios-modal-title');
        const submitBtn    = modal.querySelector('.js-usuarios-submit');
        const fieldId       = modal.querySelector('.js-usuarios-field-id');
        const fieldNombre   = modal.querySelector('.js-usuarios-field-nombre');
        const fieldUsuario  = modal.querySelector('.js-usuarios-field-usuario');
        const fieldEmail    = modal.querySelector('.js-usuarios-field-email');
        const fieldPassword = modal.querySelector('.js-usuarios-field-password');
        const fieldRol      = modal.querySelector('.js-usuarios-field-rol');

        const openModal = () => {
            modal.classList.add('is-active');
            document.body.classList.add('is-overflow-hidden');
            fieldNombre?.focus();
        };

        const closeModal = () => {
            modal.classList.remove('is-active');
            document.body.classList.remove('is-overflow-hidden');

            // Si se llegó con ?edit=ID en la URL, se limpia al cerrar para que
            // un simple refresco de página no vuelva a abrir el modal.
            const url = new URL(window.location.href);
            if (url.searchParams.has('edit')) {
                url.searchParams.delete('edit');
                window.history.replaceState({}, '', url.pathname + url.search);
            }
        };

        const resetToCreate = () => {
            form.reset();
            fieldId.value = '';
            fieldUsuario.disabled = false;
            fieldUsuario.title = '';
            fieldRol.disabled = false;
            fieldRol.title = '';
            fieldPassword.placeholder = 'Déjalo en blanco para generarla';
            title.textContent = 'Nuevo técnico';
            submitBtn.textContent = 'Crear técnico';
        };

        const fillForEdit = (btn) => {
            const data = btn.dataset;

            fieldId.value       = data.id;
            fieldNombre.value   = data.nombre;
            fieldUsuario.value  = data.usuario;
            fieldEmail.value    = data.email;
            fieldRol.value      = data.rol;
            fieldPassword.value = '';

            fieldUsuario.disabled = true;
            fieldUsuario.title = 'El usuario no se puede cambiar';

            const unoMismo = data.unoMismo === '1';
            fieldRol.disabled = unoMismo;
            fieldRol.title = unoMismo ? 'No puedes cambiar tu propio rol' : '';

            fieldPassword.placeholder = 'Déjalo en blanco para no cambiarla';
            title.textContent = 'Editar técnico';
            submitBtn.textContent = 'Guardar cambios';
        };

        document.querySelectorAll('.js-usuarios-open-modal').forEach(btn => {
            btn.addEventListener('click', () => {
                resetToCreate();
                openModal();
            });
        });

        document.querySelectorAll('.js-usuarios-edit').forEach(btn => {
            btn.addEventListener('click', () => {
                fillForEdit(btn);
                openModal();
            });
        });

        modal.querySelectorAll('.js-usuarios-close-modal').forEach(el => {
            el.addEventListener('click', closeModal);
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && modal.classList.contains('is-active')) closeModal();
        });

        // El modal ya viene abierto desde el servidor (?edit=ID o un error de
        // guardado): centra el teclado ahí sin duplicar el resto de la lógica.
        if (modal.classList.contains('is-active')) {
            document.body.classList.add('is-overflow-hidden');
            fieldNombre?.focus();
        }
    };

    // Modal de alta/edición de servicios: mismo patrón que la de técnicos, pero
    // solo con título y precio (sin usuario/rol que gestionar).
    const av_servicios_modal = () => {

        const modal = document.querySelector('.js-servicios-modal');
        if (!modal) return;

        const title            = modal.querySelector('.js-servicios-modal-title');
        const submitBtn        = modal.querySelector('.js-servicios-submit');
        const fieldId          = modal.querySelector('.js-servicios-field-id');
        const fieldTitulo      = modal.querySelector('.js-servicios-field-titulo');
        const fieldDescripcion = modal.querySelector('.js-servicios-field-descripcion');
        const fieldPrecio      = modal.querySelector('.js-servicios-field-precio');
        const fieldPrecioSin   = modal.querySelector('.js-servicios-field-precio-sin-iva');
        const form             = modal.querySelector('.js-servicios-form');

        // Precio con/sin IVA sincronizados en los dos sentidos, IVA fijo del
        // 21% (misma convención ya usada para servicios en Presupuestos):
        // se puede escribir cualquiera de los dos campos y el otro se
        // recalcula solo. Lo único que se envía al guardar es "con IVA".
        const VAT = 21;
        if (fieldPrecio && fieldPrecioSin) {
            fieldPrecioSin.addEventListener('input', () => {
                const net = parseFloat(fieldPrecioSin.value);
                fieldPrecio.value = isNaN(net) ? '' : (Math.round(net * (1 + VAT / 100) * 100) / 100).toFixed(2);
            });
            fieldPrecio.addEventListener('input', () => {
                const gross = parseFloat(fieldPrecio.value);
                fieldPrecioSin.value = isNaN(gross) ? '' : (Math.round((gross / (1 + VAT / 100)) * 100) / 100).toFixed(2);
            });
        }

        const openModal = () => {
            modal.classList.add('is-active');
            document.body.classList.add('is-overflow-hidden');
            fieldTitulo?.focus();
        };

        const closeModal = () => {
            modal.classList.remove('is-active');
            document.body.classList.remove('is-overflow-hidden');

            const url = new URL(window.location.href);
            if (url.searchParams.has('edit')) {
                url.searchParams.delete('edit');
                window.history.replaceState({}, '', url.pathname + url.search);
            }
        };

        const resetToCreate = () => {
            form.reset();
            fieldId.value = '';
            if (fieldPrecioSin) fieldPrecioSin.value = '';
            title.textContent = 'Nuevo servicio';
            submitBtn.textContent = 'Crear servicio';
        };

        const fillForEdit = (btn) => {
            const data = btn.dataset;

            fieldId.value = data.id;
            fieldTitulo.value = data.titulo;
            if (fieldDescripcion) fieldDescripcion.value = data.descripcion || '';
            fieldPrecio.value = data.precio;
            if (fieldPrecioSin) {
                const gross = parseFloat(data.precio);
                fieldPrecioSin.value = isNaN(gross) ? '' : (Math.round((gross / (1 + VAT / 100)) * 100) / 100).toFixed(2);
            }

            title.textContent = 'Editar servicio';
            submitBtn.textContent = 'Guardar cambios';
        };

        document.querySelectorAll('.js-servicios-open-modal').forEach(btn => {
            btn.addEventListener('click', () => {
                resetToCreate();
                openModal();
            });
        });

        document.querySelectorAll('.js-servicios-edit').forEach(btn => {
            btn.addEventListener('click', () => {
                fillForEdit(btn);
                openModal();
            });
        });

        modal.querySelectorAll('.js-servicios-close-modal').forEach(el => {
            el.addEventListener('click', closeModal);
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && modal.classList.contains('is-active')) closeModal();
        });

        if (modal.classList.contains('is-active')) {
            document.body.classList.add('is-overflow-hidden');
            fieldTitulo?.focus();
        }
    };

    // Modal de alta/edición de dispositivos: los campos técnicos cambian según
    // la categoría elegida, y se puede crear una categoría nueva sin salir de
    // la modal. La edición se resuelve con una navegación normal (?edit=ID:
    // el servidor ya precarga el bloque de campos técnicos que toca), así que
    // aquí solo hace falta abrir/cerrar y el comportamiento dinámico.
    const av_devices_modal = () => {

        const modal = document.querySelector('.js-devices-modal');
        if (!modal) return;

        const form      = modal.querySelector('.js-devices-form');
        const title     = modal.querySelector('.js-devices-modal-title');
        const submitBtn = modal.querySelector('.js-devices-submit');
        const fieldId     = modal.querySelector('.js-devices-field-id');
        const fieldNombre = modal.querySelector('.js-devices-field-nombre');
        const fieldCategoria = modal.querySelector('.js-devices-field-categoria');
        const specsHint  = modal.querySelector('.js-devices-specs-hint');
        const specsGroups = modal.querySelectorAll('.js-device-specs-group');

        const openModal = () => {
            modal.classList.add('is-active');
            document.body.classList.add('is-overflow-hidden');
            fieldNombre?.focus();
        };

        const closeModal = () => {
            modal.classList.remove('is-active');
            document.body.classList.remove('is-overflow-hidden');

            const url = new URL(window.location.href);
            if (url.searchParams.has('edit')) {
                url.searchParams.delete('edit');
                window.history.replaceState({}, '', url.pathname + url.search);
            }
        };

        const showSpecsGroup = (slug) => {
            let matched = false;
            specsGroups.forEach(group => {
                const isMatch = group.dataset.category === slug;
                group.classList.toggle('is-hidden', !isMatch);
                if (isMatch) matched = true;
            });
            // Categorías sin campos propios (o recién creadas) caen en el
            // bloque genérico de notas libres.
            if (!matched) {
                specsGroups.forEach(group => {
                    group.classList.toggle('is-hidden', group.dataset.category !== '__generic__');
                });
            }
            specsHint?.classList.add('is-hidden');
        };

        const hideAllSpecsGroups = () => {
            specsGroups.forEach(group => group.classList.add('is-hidden'));
            specsHint?.classList.remove('is-hidden');
        };

        const resetToCreate = () => {
            form.querySelectorAll('input[type="text"], input[type="number"], textarea').forEach(el => { el.value = ''; });
            form.querySelectorAll('input[type="checkbox"]').forEach(el => { el.checked = false; });
            if (fieldCategoria) fieldCategoria.value = '';
            if (fieldId) fieldId.value = '';
            hideAllSpecsGroups();
            title.textContent = 'Nuevo dispositivo';
            submitBtn.textContent = 'Crear dispositivo';
        };

        document.querySelectorAll('.js-devices-open-modal').forEach(btn => {
            btn.addEventListener('click', () => {
                resetToCreate();
                openModal();
            });
        });

        modal.querySelectorAll('.js-devices-close-modal').forEach(el => {
            el.addEventListener('click', closeModal);
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && modal.classList.contains('is-active')) closeModal();
        });

        // Campos técnicos según la categoría elegida
        fieldCategoria?.addEventListener('change', () => {
            const opt = fieldCategoria.selectedOptions[0];
            const slug = opt ? opt.dataset.slug : '';
            if (!slug) {
                hideAllSpecsGroups();
                return;
            }
            showSpecsGroup(slug);
        });

        // ── Crear categoría nueva sin salir de la modal ─────────────────────
        const addBtn      = modal.querySelector('.js-devices-category-add');
        const newBox       = modal.querySelector('.js-devices-category-new');
        const newInput      = modal.querySelector('.js-devices-category-new-input');
        const newConfirm    = modal.querySelector('.js-devices-category-new-confirm');
        const newCancel      = modal.querySelector('.js-devices-category-new-cancel');
        const newError        = modal.querySelector('.js-devices-category-error');
        const nonceField      = form.querySelector('[name="nonce"]');

        const openNewCategory = () => {
            newBox?.classList.remove('is-hidden');
            newError.textContent = '';
            newInput.value = '';
            newInput.focus();
        };

        const closeNewCategory = () => {
            newBox?.classList.add('is-hidden');
            newError.textContent = '';
        };

        addBtn?.addEventListener('click', openNewCategory);
        newCancel?.addEventListener('click', closeNewCategory);

        const submitNewCategory = () => {
            const nombre = (newInput.value || '').trim();
            if (!nombre) {
                newError.textContent = 'Escribe un nombre.';
                return;
            }

            newError.textContent = '';
            newConfirm.disabled = true;

            const formData = new FormData();
            formData.append('action', 'av_ajax_crear_categoria_device');
            formData.append('nonce', nonceField.value);
            formData.append('nombre', nombre);

            fetch(av_data.av_ajax_url, { method: 'POST', body: formData })
                .then(response => response.json())
                .then(results => {
                    if (!results.success) {
                        newError.textContent = (results.data && typeof results.data === 'string') ? results.data : 'No se ha podido crear la categoría.';
                        return;
                    }

                    const option = document.createElement('option');
                    option.value = results.data.id;
                    option.textContent = results.data.name;
                    option.dataset.slug = results.data.slug || '';
                    fieldCategoria.appendChild(option);
                    fieldCategoria.value = results.data.id;
                    fieldCategoria.dispatchEvent(new Event('change', { bubbles: true }));

                    closeNewCategory();
                })
                .catch(() => {
                    newError.textContent = 'Error de conexión. Inténtalo de nuevo.';
                })
                .finally(() => {
                    newConfirm.disabled = false;
                });
        };

        newConfirm?.addEventListener('click', submitNewCategory);
        newInput?.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') { e.preventDefault(); submitNewCategory(); }
        });

        if (modal.classList.contains('is-active')) {
            document.body.classList.add('is-overflow-hidden');
            fieldNombre?.focus();
        }
    };

    // Modal de alta/edición de categorías de dispositivo: un único campo
    // (nombre), mismo patrón que la de servicios.
    const av_device_categories_modal = () => {

        const modal = document.querySelector('.js-device-categories-modal');
        if (!modal) return;

        const form       = modal.querySelector('.js-device-categories-form');
        const title      = modal.querySelector('.js-device-categories-modal-title');
        const submitBtn  = modal.querySelector('.js-device-categories-submit');
        const fieldId     = modal.querySelector('.js-device-categories-field-id');
        const fieldNombre = modal.querySelector('.js-device-categories-field-nombre');

        const openModal = () => {
            modal.classList.add('is-active');
            document.body.classList.add('is-overflow-hidden');
            fieldNombre?.focus();
        };

        const closeModal = () => {
            modal.classList.remove('is-active');
            document.body.classList.remove('is-overflow-hidden');

            const url = new URL(window.location.href);
            if (url.searchParams.has('edit')) {
                url.searchParams.delete('edit');
                window.history.replaceState({}, '', url.pathname + url.search);
            }
        };

        const resetToCreate = () => {
            form.reset();
            fieldId.value = '';
            title.textContent = 'Nueva categoría';
            submitBtn.textContent = 'Crear categoría';
        };

        const fillForEdit = (btn) => {
            fieldId.value     = btn.dataset.id;
            fieldNombre.value = btn.dataset.nombre;
            title.textContent = 'Editar categoría';
            submitBtn.textContent = 'Guardar cambios';
        };

        document.querySelectorAll('.js-device-categories-open-modal').forEach(btn => {
            btn.addEventListener('click', () => {
                resetToCreate();
                openModal();
            });
        });

        document.querySelectorAll('.js-device-categories-edit').forEach(btn => {
            btn.addEventListener('click', () => {
                fillForEdit(btn);
                openModal();
            });
        });

        modal.querySelectorAll('.js-device-categories-close-modal').forEach(el => {
            el.addEventListener('click', closeModal);
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && modal.classList.contains('is-active')) closeModal();
        });

        if (modal.classList.contains('is-active')) {
            document.body.classList.add('is-overflow-hidden');
            fieldNombre?.focus();
        }
    };

    // Reordenar categorías de dispositivo arrastrando y soltando. El asa
    // (columna de la izquierda) es lo único "draggable"; se reordena la fila
    // completa (closest('tr')) y, al soltar, se guarda el orden final por
    // AJAX y se avisa de origen → destino con un aviso flotante.
    const av_device_categories_drag = () => {

        const tbody = document.querySelector('.js-device-categories-tbody');
        if (!tbody) return;

        const toast = document.querySelector('.js-device-categories-toast');
        let toastTimer = null;

        const showToast = (nombre, fromIndex, toIndex) => {
            if (!toast) return;
            toast.textContent = '';
            const strong = document.createElement('strong');
            strong.textContent = nombre;
            toast.appendChild(strong);
            toast.appendChild(document.createTextNode(
                ' movida de la posición ' + (fromIndex + 1) + ' a la ' + (toIndex + 1)
            ));
            toast.classList.remove('is-error');
            toast.classList.add('is-visible');
            clearTimeout(toastTimer);
            toastTimer = setTimeout(() => toast.classList.remove('is-visible'), 3200);
        };

        const showErrorToast = (mensaje) => {
            if (!toast) return;
            toast.textContent = mensaje;
            toast.classList.add('is-error', 'is-visible');
            clearTimeout(toastTimer);
            toastTimer = setTimeout(() => toast.classList.remove('is-visible', 'is-error'), 3200);
        };

        const rows = () => Array.from(tbody.querySelectorAll('.js-device-category-row'));

        const clearDragOverMarks = () => {
            tbody.querySelectorAll('.is-drag-over-top, .is-drag-over-bottom').forEach(row => {
                row.classList.remove('is-drag-over-top', 'is-drag-over-bottom');
            });
        };

        const guardarOrden = () => {
            const nonceField = document.querySelector('.js-device-categories-form [name="nonce"]');
            const formData = new FormData();
            formData.append('action', 'av_ajax_reordenar_device_categories');
            formData.append('nonce', nonceField ? nonceField.value : '');
            formData.append('order', JSON.stringify(rows().map(row => row.dataset.id)));

            fetch(av_data.av_ajax_url, { method: 'POST', body: formData })
                .then(response => response.json())
                .then(results => {
                    if (!results || !results.success) {
                        showErrorToast('No se ha podido guardar el nuevo orden.');
                        window.location.reload();
                    }
                })
                .catch(() => {
                    showErrorToast('Error de conexión al guardar el orden.');
                });
        };

        let draggingRow = null;
        let startIndex = -1;

        tbody.querySelectorAll('.js-device-categories-drag-handle').forEach(handle => {

            handle.addEventListener('dragstart', (e) => {
                draggingRow = handle.closest('tr');
                if (!draggingRow) return;

                startIndex = rows().indexOf(draggingRow);
                draggingRow.classList.add('is-dragging');

                e.dataTransfer.effectAllowed = 'move';
                // Firefox necesita algo en dataTransfer para permitir el arrastre
                e.dataTransfer.setData('text/plain', draggingRow.dataset.id || '');
            });

            handle.addEventListener('dragend', () => {
                if (!draggingRow) return;

                draggingRow.classList.remove('is-dragging');
                clearDragOverMarks();

                const endIndex = rows().indexOf(draggingRow);

                if (endIndex !== -1 && endIndex !== startIndex) {
                    const nombre = draggingRow.dataset.nombre || '';
                    guardarOrden();
                    showToast(nombre, startIndex, endIndex);
                }

                draggingRow = null;
                startIndex = -1;
            });

        });

        tbody.addEventListener('dragover', (e) => {
            if (!draggingRow) return;
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';

            const targetRow = e.target.closest('.js-device-category-row');
            if (!targetRow || targetRow === draggingRow) return;

            clearDragOverMarks();

            const rect = targetRow.getBoundingClientRect();
            const antes = ( e.clientY - rect.top ) < rect.height / 2;
            targetRow.classList.add(antes ? 'is-drag-over-top' : 'is-drag-over-bottom');
        });

        tbody.addEventListener('drop', (e) => {
            if (!draggingRow) return;
            e.preventDefault();

            const targetRow = e.target.closest('.js-device-category-row');
            clearDragOverMarks();
            if (!targetRow || targetRow === draggingRow) return;

            const rect = targetRow.getBoundingClientRect();
            const antes = ( e.clientY - rect.top ) < rect.height / 2;

            if (antes) {
                tbody.insertBefore(draggingRow, targetRow);
            } else {
                tbody.insertBefore(draggingRow, targetRow.nextSibling);
            }
        });

    };

    // ── Artículos: alta rápida de categoría sin salir del formulario ────────
    // Mismo patrón que av_devices_modal (categoría), pero sin la parte de
    // modal: el formulario de artículo es a página completa.
    const av_articulo_form = () => {

        const form = document.querySelector('.js-articulo-form');
        if (!form) return;

        const fieldCategoria = form.querySelector('#art-categoria');
        const addBtn         = form.querySelector('.js-articulo-category-add');
        const newBox         = form.querySelector('.js-articulo-category-new');
        const newInput       = form.querySelector('.js-articulo-category-new-input');
        const newPrefix      = form.querySelector('.js-articulo-category-new-prefix');
        const newConfirm     = form.querySelector('.js-articulo-category-new-confirm');
        const newCancel      = form.querySelector('.js-articulo-category-new-cancel');
        const newError       = form.querySelector('.js-articulo-category-error');
        const nonceField     = form.querySelector('[name="nonce"]');
        const skuField       = form.querySelector('.js-articulo-sku-field');
        const skuHint        = form.querySelector('.js-articulo-sku-hint');
        const componentSection = form.querySelector('.js-articulo-component-section');
        const componentType    = form.querySelector('.js-articulo-component-type');
        const ELECTRONICS_SLUG = 'componentes-electronicos';

        const openNewCategory = () => {
            newBox?.classList.remove('is-hidden');
            newError.textContent = '';
            newInput.value = '';
            newInput.focus();
        };

        const closeNewCategory = () => {
            newBox?.classList.add('is-hidden');
            newError.textContent = '';
        };

        addBtn?.addEventListener('click', openNewCategory);
        newCancel?.addEventListener('click', closeNewCategory);

        const submitNewCategory = () => {
            const nombre = (newInput.value || '').trim();
            const prefix = (newPrefix?.value || '').trim().toUpperCase();

            if (!nombre) {
                newError.textContent = 'Escribe un nombre.';
                return;
            }
            if (!/^[A-Z]{2,6}$/.test(prefix)) {
                newError.textContent = 'Indica un prefijo de 2 a 6 letras (p.ej. LCD, BAT, TOO…).';
                return;
            }

            newError.textContent = '';
            newConfirm.disabled = true;

            const formData = new FormData();
            formData.append('action', 'av_ajax_crear_categoria_articulo');
            formData.append('nonce', nonceField.value);
            formData.append('nombre', nombre);
            formData.append('prefix', prefix);

            fetch(av_data.av_ajax_url, { method: 'POST', body: formData })
                .then(response => response.json())
                .then(results => {
                    if (!results.success) {
                        newError.textContent = (results.data && typeof results.data === 'string') ? results.data : 'No se ha podido crear la categoría.';
                        return;
                    }

                    const option = document.createElement('option');
                    option.value = results.data.id;
                    option.textContent = results.data.name;
                    fieldCategoria.appendChild(option);
                    fieldCategoria.value = results.data.id;
                    fieldCategoria.dispatchEvent(new Event('change', { bubbles: true }));

                    closeNewCategory();
                })
                .catch(() => {
                    newError.textContent = 'Error de conexión. Inténtalo de nuevo.';
                })
                .finally(() => {
                    newConfirm.disabled = false;
                });
        };

        newConfirm?.addEventListener('click', submitNewCategory);
        newInput?.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') { e.preventDefault(); submitNewCategory(); }
        });

        // ── Mostrar/ocultar "Datos del componente electrónico" ──────────────
        // Los campos generales están siempre visibles; este bloque solo se ve
        // cuando la categoría elegida es "Componentes electrónicos". Al
        // ocultarlo no se borra ningún valor (solo display:none vía clase):
        // si el usuario cambia de categoría y vuelve, los datos siguen ahí.
        const isElectronicsSelected = () => {
            const opt = fieldCategoria?.selectedOptions[0];
            return !!(opt && opt.dataset.slug === ELECTRONICS_SLUG);
        };

        const toggleComponentSection = () => {
            if (!componentSection) return;
            const isElectronics = isElectronicsSelected();
            componentSection.classList.toggle('is-hidden', !isElectronics);
            if (componentType) componentType.required = isElectronics;
        };

        fieldCategoria?.addEventListener('change', toggleComponentSection);

        // ── Vista previa del SKU al elegir categoría / tipo de componente
        // (solo al crear) ────────────────────────────────────────────────
        // El número definitivo se reserva y valida en el backend al guardar
        // (av_articulo_generate_sku): esto es solo una vista previa, nunca el
        // valor que se envía como autoritativo (el backend ignora "sku"). Para
        // "Componentes electrónicos" el prefijo depende del tipo de
        // componente, no de la categoría, así que también se recalcula al
        // cambiar ese select.
        if (skuField && skuField.dataset.mode === 'create') {
            const updateSkuPreview = () => {
                const categoryId = fieldCategoria.value;
                if (!categoryId) {
                    skuField.value = '';
                    skuHint?.classList.add('is-hidden');
                    return;
                }

                // Si la categoría es electrónicos pero aún no se ha elegido
                // tipo de componente, no hay prefijo definitivo que mostrar:
                // se espera a que el usuario lo seleccione en vez de enseñar
                // un SKU "ELE-..." que nunca sería el real.
                if (isElectronicsSelected() && !(componentType?.value)) {
                    skuField.value = '';
                    skuHint?.classList.add('is-hidden');
                    return;
                }

                skuField.value = 'Generando…';

                const formData = new FormData();
                formData.append('action', 'av_ajax_preview_articulo_sku');
                formData.append('nonce', nonceField.value);
                formData.append('category', categoryId);
                if (componentType?.value) formData.append('component_type', componentType.value);

                fetch(av_data.av_ajax_url, { method: 'POST', body: formData })
                    .then(response => response.json())
                    .then(results => {
                        if (results.success && results.data && results.data.sku) {
                            skuField.value = results.data.sku;
                            skuHint?.classList.remove('is-hidden');
                        } else {
                            skuField.value = '';
                        }
                    })
                    .catch(() => {
                        skuField.value = '';
                    });
            };

            fieldCategoria?.addEventListener('change', updateSkuPreview);
            componentType?.addEventListener('change', updateSkuPreview);
        }
    };

    // ── Artículos: modal "Ajustar stock" ────────────────────────────────────
    // Un único modal en el DOM (c-articulo-stock-modal.php), reutilizado tanto
    // desde el listado (varios botones, uno por fila) como desde la propia
    // ficha del artículo: se rellena con los data-* del botón pulsado.
    const av_articulo_stock_modal = () => {

        const modal = document.querySelector('.js-articulo-stock-modal');
        if (!modal) return;

        const fieldId     = modal.querySelector('.js-articulo-stock-id');
        const fieldActual = modal.querySelector('.js-articulo-stock-actual');
        const fieldNueva  = modal.querySelector('.js-articulo-stock-nueva-cantidad');
        const nombreEl    = modal.querySelector('.js-articulo-stock-nombre');
        const errorEl     = modal.querySelector('.js-articulo-stock-error');
        const form        = modal.querySelector('.js-articulo-stock-form');

        const openModal = (btn) => {
            fieldId.value = btn.dataset.id || '';
            fieldActual.value = btn.dataset.stock || '0';
            fieldNueva.value = btn.dataset.stock || '0';
            nombreEl.textContent = btn.dataset.nombre || '';
            errorEl.textContent = '';
            modal.classList.add('is-active');
            document.body.classList.add('is-overflow-hidden');
            fieldNueva.focus();
        };

        const closeModal = () => {
            modal.classList.remove('is-active');
            document.body.classList.remove('is-overflow-hidden');
        };

        document.addEventListener('click', (e) => {
            const btn = e.target.closest('.js-articulo-stock-open');
            if (btn) { e.preventDefault(); openModal(btn); }
        });

        modal.querySelector('.js-articulo-stock-cancel')?.addEventListener('click', closeModal);

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && modal.classList.contains('is-active')) closeModal();
        });

        form?.addEventListener('submit', (e) => {
            const nueva = parseFloat((fieldNueva.value || '').replace(',', '.'));
            if (isNaN(nueva)) {
                e.preventDefault();
                errorEl.textContent = 'Indica una cantidad válida.';
            }
        });
    };

    const av_articulos_filter_async = () => {
        av_init_async_filter({
            formSelector: '.js-articulos-filter-form',
            countId: 'articulos-count',
            listId: 'articulos-list',
            clearFiltersId: 'articulos-clear-filters',
            loaderSelector: '.js-articulos-loader',
            submitSelector: '.js-articulos-filter-submit',
            ajaxAction: 'av_ajax_filter_articulos',
            includeUrlFilterParam: false
        })
    }

    const av_sats_filter_async = () => {
        av_init_async_filter({
            formSelector: '.js-sats-filter-form',
            countId: 'sats-count',
            listId: 'sats-list',
            clearFiltersId: 'sats-clear-filters',
            loaderSelector: '.js-sats-loader',
            submitSelector: '.js-sats-filter-submit',
            ajaxAction: 'av_ajax_filter_sats',
            includeUrlFilterParam: true,
            onResultsUpdated: () => {
                av_sync_sats_tabs()
                // Las filas se han sustituido: hay que re-vincular sus
                // controles (cambiar/guardar estado), que se enlazan por
                // elemento y no por delegacion.
                av_enable_button_save_status()
                if (window.av_rebind_sats_save_status) {
                    window.av_rebind_sats_save_status()
                } else {
                    av_save_status()
                }
            }
        })
    }

    const av_clients_filter_async = () => {
        av_init_async_filter({
            formSelector: '.js-clients-filter-form',
            countId: 'clients-count',
            listId: 'clients-list',
            clearFiltersId: 'clients-clear-filters',
            loaderSelector: '.js-clients-loader',
            submitSelector: '.js-clients-filter-submit',
            ajaxAction: 'av_ajax_filter_clients',
            includeUrlFilterParam: false
        })
    }

    const av_facturas_filter_async = () => {
        av_init_async_filter({
            formSelector: '.js-facturas-filter-form',
            countId: 'facturas-count',
            listId: 'facturas-list',
            clearFiltersId: 'facturas-clear-filters',
            loaderSelector: '.js-facturas-loader',
            submitSelector: '.js-facturas-filter-submit',
            ajaxAction: 'av_ajax_filter_facturas',
            includeUrlFilterParam: false
        })
    }

    const av_pedidos_filter_async = () => {
        av_init_async_filter({
            formSelector: '.js-pedidos-filter-form',
            countId: 'pedidos-count',
            listId: 'pedidos-list',
            clearFiltersId: 'pedidos-clear-filters',
            loaderSelector: '.js-pedidos-loader',
            submitSelector: '.js-pedidos-filter-submit',
            ajaxAction: 'av_ajax_filter_pedidos',
            includeUrlFilterParam: false
        })
    }

    // Validación "entregado sin tipo de pago" en el formulario de pedido:
    // mismo mensaje/mecanismo que el SAT (av_sat_form_validate_finalizar(),
    // más arriba en este archivo) — bloquea el envío en el frontend. El
    // backend tiene la misma comprobación (av_pedido_validate_delivered_payment()
    // en inc/pedidos/crud.php): esto es solo la primera línea de defensa.
    const av_pedido_form_validate_delivered = () => {

        const formulario = document.querySelector('.js-pedido-form')
        if (!formulario) return

        formulario.addEventListener('submit', (e) => {

            // Anticipo indicado → su forma de pago es obligatoria (el toggle
            // del SAT, av_sat_form__anticipo_toggle, muestra/oculta el campo).
            const anticipoInput = formulario.querySelector('[name="anticipo"]')
            const anticipoValue = parseFloat((anticipoInput?.value || '').replace(',', '.'))
            if (!isNaN(anticipoValue) && anticipoValue > 0) {
                const anticipoPayment = formulario.querySelector('[name="anticipo-payment"]')
                if (!anticipoPayment || !anticipoPayment.value) {
                    e.preventDefault()
                    alert('Has indicado un anticipo: selecciona su forma de pago.')
                    anticipoPayment?.focus()
                    return
                }
            }

            const estadoSelect = formulario.querySelector('[name="status"]')
            if (!estadoSelect || estadoSelect.value !== 'delivered') return

            const paymentSelect = formulario.querySelector('[name="price-description"]')
            if (!paymentSelect || !paymentSelect.value) {
                e.preventDefault()
                alert('Antes de marcar el pedido como entregado debes indicar el tipo de pago.')
                paymentSelect?.focus()
            }
        })
    }

    // "Generar factura" / "Ver factura": ya no es un botón AJAX — es un
    // enlace <a target="_blank"> directo a av_pedido_invoice_page()
    // (inc/pedidos/venta.php), exactamente igual que el botón de factura del
    // SAT (js-invoice-btn, que tampoco lleva JS). No hace falta manejador.

    // "Marcar entregado" / "Cancelar pedido" del formulario de pedido: ponen
    // el estado en el desplegable y guardan TODO el formulario (así no se
    // pierde nada de lo editado). Para entregar, además, se rellena la fecha
    // de entrega si está vacía; el tipo de pago sigue siendo obligatorio
    // (av_pedido_form_validate_delivered bloquea el envío si falta). El
    // "marcar recibido" de cada línea vive en av_pedido_lines_widget().
    // "Guardar cambios" (edición de un pedido) solo se enseña una vez algo
    // del formulario ha cambiado de verdad respecto a como se cargó la
    // página — el botón empieza oculto en el HTML (clase js-pedido-save-btn,
    // ver components/c-pedido-form.php) y aquí se compara el estado actual
    // del formulario contra la foto inicial en cada "input"/"change". No
    // aplica a "Crear pedido" (no tiene esta clase, así que av_call_fn ni
    // siquiera llama a esta función).
    const av_pedido_dirty_save_button = () => {

        const form    = document.querySelector('.js-pedido-form')
        const saveBtn = form?.querySelector('.js-pedido-save-btn')
        if (!form || !saveBtn) return

        const snapshot = () => new URLSearchParams(new FormData(form)).toString()
        const initial  = snapshot()

        const check = () => {
            saveBtn.classList.toggle('is-hidden', snapshot() === initial)
        }

        form.addEventListener('input', check)
        form.addEventListener('change', check)
    }

    const av_pedido_set_status_buttons = () => {

        const form = document.querySelector('.js-pedido-form')
        if (!form) return

        form.querySelectorAll('.js-pedido-set-status').forEach(btn => {
            btn.addEventListener('click', () => {
                if (btn.dataset.confirm && !confirm(btn.dataset.confirm)) return

                const select = form.querySelector('[name="status"]')
                if (!select) return
                select.value = btn.dataset.status
                select.dispatchEvent(new Event('change', { bubbles: true }))

                if (btn.dataset.status === 'delivered') {
                    const delivered = form.querySelector('[name="delivered-date"]')
                    if (delivered && !delivered.value) {
                        const d = new Date()
                        const pad = (n) => String(n).padStart(2, '0')
                        delivered.value = `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`
                        delivered.dispatchEvent(new Event('input', { bubbles: true }))
                    }
                }

                form.requestSubmit()
            })
        })
    }

    const av_filter_all = () => {

        const node = document.querySelectorAll('.js-filter-all')

        // Pestaña activa segun la URL, o "Todos" si se llega con una busqueda hecha
        av_sync_sats_tabs()

        node.forEach(e => {
            e.addEventListener('click', () => {

                // Sin atajo si ya esta activa: al venir de una busqueda, volver a
                // pulsar la pestaña es la forma de limpiar los filtros.
                const getCurrentData = e.dataset.id;
                let pathname = window.location.pathname;

                // Eliminar /page/X/
                pathname = pathname.replace(/\/page\/\d+\/?/, '/');
                const newUrl = window.location.origin + pathname + '?filter=' + getCurrentData;
                
                window.location.href = newUrl;
            });
        }
        )

    }

    // Segmentado Particular/Profesional dentro del propio formulario de cliente
    // (sustituye al <select> nativo): dos botones que escriben directamente en
    // el input oculto "type-client" que se envía con el resto del formulario.
    const av_client_type_toggle = () => {

        document.querySelectorAll('.c-client-form').forEach(form => {

            const hidden      = form.querySelector('.js-client-form__type-client')
            const btns        = form.querySelectorAll('.js-client-type-btn')
            const grid        = form.querySelector('.js-client-form__grid')
            const nombreLabel = form.querySelector('.js-client-form__nombre-label')
            const nombreInput = form.querySelector('.js-client-form__nombre-input')
            if (!hidden || !btns.length) return

            btns.forEach(btn => {
                btn.addEventListener('click', () => {
                    if (btn.classList.contains('is-active')) return

                    btns.forEach(b => b.classList.remove('is-active'))
                    btn.classList.add('is-active')
                    hidden.value = btn.dataset.value

                    const isProfesional = btn.dataset.value === 'profesional'
                    const nombreLabelText = isProfesional ? 'Razón social' : 'Nombre y apellido / Empresa'

                    if (grid) grid.classList.toggle('is-profesional', isProfesional)
                    if (nombreLabel) nombreLabel.textContent = nombreLabelText
                    if (nombreInput) nombreInput.placeholder = nombreLabelText
                })
            })

        })

    }

    // Compras a particulares: filas de artículos del formulario (ver
    // components/c-compra-form.php). "Añadir otro artículo" clona la fila
    // plantilla (<template>), la "×" quita la fila (si es la única, solo la
    // vacía) y el total se recalcula al escribir cualquier precio.
    const av_compra_items_widget = () => {

        const widget = document.querySelector('.js-compra-items');
        if (!widget) return

        const list     = widget.querySelector('.js-compra-items-list')
        const tpl      = widget.querySelector('.js-compra-item-template')
        const addBtn   = widget.querySelector('.js-compra-item-add')
        const totalEl  = widget.querySelector('.js-compra-total')

        const recalc = () => {
            let total = 0
            widget.querySelectorAll('.js-compra-item-precio').forEach(inp => {
                const v = parseFloat(String(inp.value).replace(',', '.'))
                if (!isNaN(v)) total += v
            })
            if (totalEl) totalEl.textContent = total.toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' €'
        }

        addBtn?.addEventListener('click', () => {
            if (!tpl) return
            list.appendChild(tpl.content.cloneNode(true))
            list.lastElementChild?.querySelector('input')?.focus()
        })

        list.addEventListener('click', (e) => {
            const btn = e.target.closest('.js-compra-item-remove')
            if (!btn) return
            const row = btn.closest('.js-compra-item')
            if (list.querySelectorAll('.js-compra-item').length > 1) {
                row.remove()
            } else {
                row.querySelectorAll('input').forEach(i => { i.value = '' })
            }
            recalc()
            // Avisa al formulario (botón "Guardar solo si hay cambios").
            list.dispatchEvent(new Event('change', { bubbles: true }))
        })

        list.addEventListener('input', (e) => {
            if (e.target.classList.contains('js-compra-item-precio')) recalc()
        })

        recalc()
    }

    // Segundo teléfono del formulario de cliente (opcional, hasta 2 por
    // cliente): empieza oculto tras "+ Añadir teléfono" salvo que ya venga
    // relleno al editar un cliente que lo tenga (ver $phone2_open en
    // components/c-client-form.php). El tipo Fijo/Móvil solo cambia si se ve
    // o no el campo de extensión — un fijo no lleva prefijo de móvil.
    const av_client_phone2_toggle = () => {

        document.querySelectorAll('.c-client-form').forEach(form => {

            const addBtn     = form.querySelector('.js-client-form__phone2-toggle')
            const block      = form.querySelector('.js-client-form__phone2')
            const removeBtn  = form.querySelector('.js-client-form__phone2-remove')
            const typeSelect = form.querySelector('.js-client-form__phone2-type')
            const extInput   = form.querySelector('.js-client-form__phone2-extension')
            if (!addBtn || !block) return

            addBtn.addEventListener('click', () => {
                block.classList.remove('is-hidden')
                addBtn.classList.add('is-hidden')
            })

            removeBtn?.addEventListener('click', () => {
                block.classList.add('is-hidden')
                addBtn.classList.remove('is-hidden')
                // Se vacía al quitarlo: si se vuelve a pulsar "+ Añadir
                // teléfono" más tarde, no se manda sin querer un número a
                // medio rellenar de antes.
                const numberInput = block.querySelector('[name="telefono-2"]')
                if (numberInput) numberInput.value = ''
                if (typeSelect) typeSelect.value = 'movil'
                extInput?.classList.remove('is-hidden')
            })

            typeSelect?.addEventListener('change', () => {
                extInput?.classList.toggle('is-hidden', typeSelect.value === 'fijo')
            })

        })

    }

    // Segmentado Todos/Particulares/Profesionales del LISTADO de clientes
    // (no confundir con av_client_type_toggle, que es el del formulario de
    // alta): escribe en el campo oculto "tipo-cliente" del formulario de
    // filtros (ver components/c-filters.php, type=>'hidden') y relanza la
    // búsqueda async al momento, sin esperar al botón "Buscar" — mismo efecto
    // que escribir a mano en el <select> que sustituye.
    // Segmentado "pill" genérico para un filtro oculto dentro de un buscador
    // async ya existente (mismo patrón que .js-filter-all de SATs, pero sin
    // recargar la página: escribe en el campo oculto y relanza la búsqueda
    // async con form.requestSubmit(), igual que "Limpiar campo" ya hace en
    // av_init_async_filter). Un único sitio para no duplicar esto por cada
    // listado que quiera sus propias 3 pestañas Todos/En curso/Finalizados
    // (Clientes, Pedidos...) — todos reutilizan el mismo diseño y comportamiento.
    const av_pill_tab_filter = (widgetSelector, formSelector, hiddenSelector) => {

        const widget = document.querySelector(widgetSelector)
        const form   = document.querySelector(formSelector)
        if (!widget || !form) return

        const hidden = form.querySelector(hiddenSelector)
        const btns   = widget.querySelectorAll('.js-pill-tab-btn')
        if (!hidden || !btns.length) return

        const activeClass = 'c-list-cpt-sats__menu-item--active'

        btns.forEach(btn => {
            btn.addEventListener('click', () => {
                if (btn.classList.contains(activeClass)) return

                btns.forEach(b => b.classList.remove(activeClass))
                btn.classList.add(activeClass)
                hidden.value = btn.dataset.value
                form.requestSubmit()
            })
        })

    }

    // Modal de alta rápida de cliente: se reutiliza tal cual en el listado de
    // clientes y en el paso "elige cliente" al crear un SAT nuevo. Solo puede
    // haber una instancia por página, así que no hace falta más que abrir/cerrar.
    const av_client_modal = () => {

        const modal = document.querySelector('.js-client-modal')
        if (!modal) return

        const firstField = modal.querySelector('.c-client-form__input-name')

        const openModal = () => {
            modal.classList.add('is-active')
            document.body.classList.add('is-overflow-hidden')
            firstField?.focus()
        }

        const closeModal = () => {
            modal.classList.remove('is-active')
            document.body.classList.remove('is-overflow-hidden')
        }

        document.querySelectorAll('.js-client-modal-open').forEach(btn => {
            btn.addEventListener('click', openModal)
        })

        modal.querySelectorAll('.js-client-modal-close').forEach(el => {
            el.addEventListener('click', closeModal)
        })

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && modal.classList.contains('is-active')) closeModal()
        })

    }


    // END GLOBAL FUNCTIONS ---------------------------- 

    // Configuración → WhatsApp: revela el campo de sustitución de un secreto
    // (Access Token / App Secret / Verify Token) ya guardado. El valor real
    // nunca llega al navegador, solo se puede escribir uno nuevo.
    const av_whatsapp_secret_toggle = () => {
        document.querySelectorAll('.js-wa-secret-change').forEach(btn => {
            btn.addEventListener('click', () => {
                const wrapper = btn.closest('.js-wa-secret-set');
                const input   = wrapper ? wrapper.nextElementSibling : null;
                if (!wrapper || !input) return;

                wrapper.classList.add('c-cfg__wa-hidden');
                input.classList.remove('c-cfg__wa-hidden');
                input.focus();
            });
        });
    };

    // Configuración → WhatsApp: botón "Probar conexión" — llamada real al
    // backend (que a su vez llama a Meta), sin recargar la página.
    const av_whatsapp_test_connection_btn = () => {
        const btn = document.querySelector('.js-whatsapp-test-connection');
        if (!btn) return;

        const statusEl  = document.querySelector('.js-whatsapp-status');
        const messageEl = document.querySelector('.js-whatsapp-status-message');

        btn.addEventListener('click', () => {
            const originalText = btn.textContent;
            btn.disabled = true;
            btn.textContent = 'Probando...';

            fetch(`${av_data.rest_url}sat/v1/whatsapp/settings/test-connection`, {
                method: 'POST',
                headers: { 'X-WP-Nonce': av_data.rest_nonce },
            })
            .then(res => res.json())
            .then(data => {
                if (statusEl) {
                    statusEl.className = 'js-whatsapp-status c-cfg__wa-status c-cfg__wa-status--' + (data.success ? 'connected' : 'error');
                    statusEl.textContent = data.success ? '🟢 Conectado' : '🔴 Error de conexión';
                }
                if (messageEl) {
                    messageEl.textContent = data.success ? '' : (data.message || '');
                }
            })
            .catch(() => {
                if (statusEl) {
                    statusEl.className = 'js-whatsapp-status c-cfg__wa-status c-cfg__wa-status--error';
                    statusEl.textContent = '🔴 Error de conexión';
                }
                if (messageEl) messageEl.textContent = 'No se ha podido contactar con el servidor.';
            })
            .finally(() => {
                btn.disabled = false;
                btn.textContent = originalText;
            });
        });
    };

    // ── Inbox de WhatsApp (Fase 5, solo lectura: sin envío todavía) ─────────

    const av_whatsapp_rest = (path, opts = {}) => {
        return fetch(`${av_data.rest_url}sat/v1/whatsapp/${path}`, {
            ...opts,
            headers: { 'X-WP-Nonce': av_data.rest_nonce, ...(opts.headers || {}) },
        }).then(res => res.json());
    };

    const av_whatsapp_escape_html = (str) => (str || '').toString()
        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');

    // Botón "💬 WhatsApp" en la ficha de cliente: SOLO abre la conversación
    // existente (o muestra que todavía no hay ninguna). Nunca envía nada ni
    // crea una conversación por sí mismo.
    const av_whatsapp_open_button = () => {
        document.querySelectorAll('.js-whatsapp-open-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                if (btn.classList.contains('is-loading')) return;

                const clientId = btn.dataset.clientId || '';
                const emptyEl  = btn.closest('form, .c-sat-whatsapp-card')?.querySelector('.js-whatsapp-empty-msg');

                const params = new URLSearchParams();
                if (clientId) params.set('client_id', clientId);

                btn.classList.add('is-loading');
                av_whatsapp_rest(`find-conversation?${params.toString()}`)
                    .then(data => {
                        if (data.conversation_id) {
                            window.location.href = `${av_data.whatsapp_url}?conversation=${data.conversation_id}`;
                        } else if (emptyEl) {
                            emptyEl.classList.remove('is-hidden');
                        }
                    })
                    .finally(() => btn.classList.remove('is-loading'));
            });
        });
    };

    // Sección "WhatsApp" del SAT: solo lectura, muestra la conversación
    // asociada si existe (por teléfono del cliente del SAT) o un estado vacío.
    const av_whatsapp_sat_card = () => {
        const card = document.querySelector('.js-whatsapp-sat-card');
        if (!card) return;

        const satId = card.dataset.satId;

        av_whatsapp_rest(`find-conversation?sat_id=${satId}`)
            .then(data => {
                if (!data.conversation_id) {
                    card.innerHTML = '<div class="c-sat-whatsapp-card__empty">Sin conversación de WhatsApp.</div>';
                    return;
                }
                return av_whatsapp_rest(`conversations/${data.conversation_id}`).then(conv => {
                    const name = conv.contact_name || (conv.customer && conv.customer.name) || conv.phone_number;
                    card.innerHTML = `
                        <div class="c-sat-whatsapp-card__row">
                            <div class="c-sat-whatsapp-card__name">${av_whatsapp_escape_html(name)}</div>
                            <div class="c-sat-whatsapp-card__preview">${av_whatsapp_escape_html(conv.last_message_preview || '')}</div>
                            <div class="c-sat-whatsapp-card__time">${av_whatsapp_escape_html(conv.last_message_at || '')}</div>
                        </div>
                        <a class="c-sat-whatsapp-card__open o-button o-button--style-1" href="${av_data.whatsapp_url}?conversation=${conv.id}">Abrir conversación</a>
                    `;
                });
            })
            .catch(() => {
                card.innerHTML = '<div class="c-sat-whatsapp-card__empty">No se ha podido cargar.</div>';
            });
    };

    // Badge del menú lateral: compartido entre el propio inbox (cuando está
    // abierto) y el resto de páginas del CRM (donde solo se refresca el número).
    const av_whatsapp_apply_badge = (count) => {
        const badge = document.querySelector('.js-whatsapp-nav-badge');
        if (!badge) return;
        if (count > 0) {
            badge.textContent = count > 99 ? '99+' : String(count);
            badge.classList.remove('is-hidden');
        } else {
            badge.classList.add('is-hidden');
        }
    };

    const av_whatsapp_refresh_badge = () => {
        av_whatsapp_rest('unread-count')
            .then(data => av_whatsapp_apply_badge(data.unread_conversations))
            .catch(() => {});
    };

    // Badge en páginas SIN el inbox abierto: polling ligero (20s) y solo
    // mientras la pestaña esté visible. Si el inbox está en esta misma página,
    // su propio polling (más frecuente) ya se encarga del badge, así que este
    // no arranca un segundo intervalo redundante contra el mismo endpoint.
    const av_whatsapp_nav_badge_poll = () => {
        const badge = document.querySelector('.js-whatsapp-nav-badge');
        if (!badge || document.querySelector('.js-whatsapp-inbox')) return;

        let timer = null;
        const start = () => { if (!timer) { av_whatsapp_refresh_badge(); timer = setInterval(av_whatsapp_refresh_badge, 20000); } };
        const stop = () => { clearInterval(timer); timer = null; };

        document.addEventListener('visibilitychange', () => { document.hidden ? stop() : start(); });
        if (!document.hidden) start();
    };

    const av_whatsapp_inbox = () => {
        const root = document.querySelector('.js-whatsapp-inbox');
        if (!root) return;

        const escapeHtml = (str) => (str || '').toString()
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');

        const formatTime = (mysqlDate) => {
            if (!mysqlDate) return '';
            const d = new Date(mysqlDate.replace(' ', 'T'));
            if (isNaN(d.getTime())) return '';
            const sameDay = d.toDateString() === new Date().toDateString();
            return sameDay
                ? d.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' })
                : d.toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit' });
        };

        const listEl        = root.querySelector('.js-whatsapp-list');
        const listLoadingEl = root.querySelector('.js-whatsapp-list-loading');
        const listEmptyEl   = root.querySelector('.js-whatsapp-list-empty');
        const loadMoreBtn   = root.querySelector('.js-whatsapp-load-more-conversations');
        const searchInput   = root.querySelector('.js-whatsapp-search');
        const filterBtns    = root.querySelectorAll('.js-whatsapp-filter');
        const chatEmptyEl   = root.querySelector('.js-whatsapp-chat-empty');
        const chatEl        = root.querySelector('.js-whatsapp-chat');
        const chatNameEl    = root.querySelector('.js-whatsapp-chat-name');
        const chatMetaEl    = root.querySelector('.js-whatsapp-chat-meta');
        const messagesWrap  = root.querySelector('.js-whatsapp-messages');
        const messagesListEl= root.querySelector('.js-whatsapp-messages-list');
        const loadOlderBtn  = root.querySelector('.js-whatsapp-load-older');
        const backBtn       = root.querySelector('.js-whatsapp-back');

        let currentFilter = 'all';
        let currentSearch = '';
        let currentPage = 1;
        let totalPages = 1;
        let loadingList = false;
        let openConversationId = null;
        let oldestMessageId = null;
        let newestMessageId = null;
        let lastActivitySeen = '';
        let searchDebounce = null;

        const renderConversationRow = (conv) => {
            const displayName = conv.contact_name || conv.customer_name || conv.phone_number;
            const metaLine = conv.sat_label || conv.customer_name || '';
            const initial = (displayName || '?').trim().charAt(0).toUpperCase() || '?';
            return `
                <a href="#" class="c-whatsapp-inbox__row js-whatsapp-row${conv.id === openConversationId ? ' is-active' : ''}" data-id="${conv.id}">
                    <div class="c-whatsapp-inbox__row-avatar">${escapeHtml(initial)}</div>
                    <div class="c-whatsapp-inbox__row-body">
                        <div class="c-whatsapp-inbox__row-top">
                            <span class="c-whatsapp-inbox__row-name">${escapeHtml(displayName)}</span>
                            <span class="c-whatsapp-inbox__row-time">${formatTime(conv.last_message_at)}</span>
                        </div>
                        ${metaLine ? `<div class="c-whatsapp-inbox__row-device">${escapeHtml(metaLine)}</div>` : ''}
                        <div class="c-whatsapp-inbox__row-bottom">
                            <span class="c-whatsapp-inbox__row-preview">${escapeHtml(conv.last_message_preview || '')}</span>
                            ${conv.unread_count > 0 ? `<span class="c-whatsapp-inbox__row-badge">${conv.unread_count > 99 ? '99+' : conv.unread_count}</span>` : ''}
                        </div>
                    </div>
                </a>
            `;
        };

        const loadConversations = (reset) => {
            if (loadingList) return;
            loadingList = true;
            if (reset) { currentPage = 1; listEl.innerHTML = ''; }
            listLoadingEl.classList.remove('is-hidden');
            listEmptyEl.classList.add('is-hidden');

            const params = new URLSearchParams({ filter: currentFilter, search: currentSearch, page: currentPage });
            av_whatsapp_rest(`conversations?${params.toString()}`)
                .then(data => {
                    totalPages = data.total_pages || 1;
                    const items = data.items || [];
                    if (reset && !items.length) {
                        listEmptyEl.textContent = currentSearch || currentFilter !== 'all'
                            ? 'No se han encontrado conversaciones.'
                            : 'Todavía no hay conversaciones de WhatsApp.';
                        listEmptyEl.classList.remove('is-hidden');
                    } else {
                        listEl.insertAdjacentHTML('beforeend', items.map(renderConversationRow).join(''));
                    }
                    loadMoreBtn.classList.toggle('is-hidden', currentPage >= totalPages);
                })
                .catch(() => {
                    listEmptyEl.textContent = 'No se han podido cargar las conversaciones.';
                    listEmptyEl.classList.remove('is-hidden');
                })
                .finally(() => {
                    loadingList = false;
                    listLoadingEl.classList.add('is-hidden');
                });
        };

        loadMoreBtn?.addEventListener('click', () => {
            if (loadingList || currentPage >= totalPages) return;
            currentPage++;
            loadConversations(false);
        });

        searchInput?.addEventListener('input', () => {
            clearTimeout(searchDebounce);
            searchDebounce = setTimeout(() => {
                currentSearch = searchInput.value.trim();
                loadConversations(true);
            }, 300);
        });

        filterBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                if (btn.classList.contains('is-active')) return;
                filterBtns.forEach(b => b.classList.remove('is-active'));
                btn.classList.add('is-active');
                currentFilter = btn.dataset.filter;
                loadConversations(true);
            });
        });

        listEl?.addEventListener('click', (e) => {
            const row = e.target.closest('.js-whatsapp-row');
            if (!row) return;
            e.preventDefault();
            openConversation(parseInt(row.dataset.id, 10));
        });

        const messageStatusIcon = (msg) => {
            if (msg.direction !== 'outbound') return '';
            const map = {
                pending: '⌛', sent: '✓', delivered: '✓✓',
                read: '<span class="is-read">✓✓</span>', failed: '<span class="is-failed">❌</span>',
            };
            return `<span class="c-whatsapp-inbox__msg-status">${map[msg.status] || ''}</span>`;
        };

        const MESSAGE_TYPE_LABELS = {
            image: '📷 Imagen', video: '🎬 Vídeo', audio: '🎵 Audio', document: '📄 Documento',
            sticker: '🩹 Sticker', location: '📍 Ubicación', contacts: '👤 Contacto',
        };

        const renderMessageContent = (msg) => {
            if (msg.message_type === 'text') {
                return `<div class="c-whatsapp-inbox__msg-text">${escapeHtml(msg.text_body || '')}</div>`;
            }
            if (msg.media_url && (msg.message_type === 'image' || msg.message_type === 'sticker')) {
                return `<a href="${escapeHtml(msg.media_url)}" target="_blank" rel="noopener">`
                    + `<img class="c-whatsapp-inbox__msg-image" src="${escapeHtml(msg.media_url)}" alt=""></a>`
                    + (msg.caption ? `<div class="c-whatsapp-inbox__msg-caption">${escapeHtml(msg.caption)}</div>` : '');
            }
            if (msg.media_url) {
                return `<a class="c-whatsapp-inbox__msg-file" href="${escapeHtml(msg.media_url)}" target="_blank" rel="noopener">📎 ${escapeHtml(msg.filename || 'Archivo')}</a>`
                    + (msg.caption ? `<div class="c-whatsapp-inbox__msg-caption">${escapeHtml(msg.caption)}</div>` : '');
            }
            return `<div class="c-whatsapp-inbox__msg-placeholder">${escapeHtml(MESSAGE_TYPE_LABELS[msg.message_type] || 'Mensaje')}</div>`;
        };

        const renderMessage = (msg) => `
            <div class="c-whatsapp-inbox__msg c-whatsapp-inbox__msg--${msg.direction === 'outbound' ? 'out' : 'in'}" data-id="${msg.id}">
                <div class="c-whatsapp-inbox__msg-bubble">
                    ${renderMessageContent(msg)}
                    <div class="c-whatsapp-inbox__msg-foot">
                        <span class="c-whatsapp-inbox__msg-time">${formatTime(msg.wa_timestamp || msg.created_at)}</span>
                        ${messageStatusIcon(msg)}
                    </div>
                    ${msg.status === 'failed' && msg.error_message ? `<div class="c-whatsapp-inbox__msg-error">${escapeHtml(msg.error_message)}</div>` : ''}
                </div>
            </div>
        `;

        const scrollMessagesToBottom = () => {
            if (messagesWrap) messagesWrap.scrollTop = messagesWrap.scrollHeight;
        };

        const markActiveRow = (id) => {
            root.querySelectorAll('.js-whatsapp-row').forEach(r => {
                r.classList.toggle('is-active', parseInt(r.dataset.id, 10) === id);
            });
        };

        const clearRowBadge = (id) => {
            const row = root.querySelector(`.js-whatsapp-row[data-id="${id}"]`);
            row?.querySelector('.c-whatsapp-inbox__row-badge')?.remove();
        };

        const openConversation = (id) => {
            openConversationId = id;
            oldestMessageId = null;
            newestMessageId = null;

            markActiveRow(id);
            chatEmptyEl.classList.add('is-hidden');
            chatEl.classList.remove('is-hidden');
            root.classList.add('is-chat-open');
            messagesListEl.innerHTML = '<div class="c-whatsapp-inbox__messages-loading">Cargando...</div>';
            loadOlderBtn.classList.add('is-hidden');
            chatNameEl.textContent = '';
            chatMetaEl.textContent = '';

            av_whatsapp_rest(`conversations/${id}`)
                .then(conv => {
                    if (openConversationId !== id) return; // el usuario ya cambió de conversación
                    chatNameEl.textContent = conv.contact_name || (conv.customer && conv.customer.name) || conv.phone_number;
                    const metaParts = [];
                    if (conv.phone_number) metaParts.push('+' + conv.phone_number);
                    if (conv.sat) metaParts.push(conv.sat.label);
                    chatMetaEl.textContent = metaParts.join(' · ');
                })
                .catch(() => {});

            av_whatsapp_rest(`conversations/${id}/messages?limit=30`)
                .then(data => {
                    if (openConversationId !== id) return;
                    const msgs = data.messages || [];
                    messagesListEl.innerHTML = msgs.length
                        ? msgs.map(renderMessage).join('')
                        : '<div class="c-whatsapp-inbox__messages-empty">Todavía no hay mensajes.</div>';
                    if (msgs.length) {
                        oldestMessageId = msgs[0].id;
                        newestMessageId = msgs[msgs.length - 1].id;
                    }
                    loadOlderBtn.classList.toggle('is-hidden', !data.has_more);
                    scrollMessagesToBottom();
                })
                .catch(() => {
                    messagesListEl.innerHTML = '<div class="c-whatsapp-inbox__messages-empty">No se han podido cargar los mensajes.</div>';
                });

            // Solo marca como leído en NUESTRO inbox (unread_count = 0). No manda
            // "read" a Meta — eso, si se hace, será una fase aparte.
            av_whatsapp_rest(`conversations/${id}/read`, { method: 'POST' })
                .then(() => { clearRowBadge(id); av_whatsapp_refresh_badge(); })
                .catch(() => {});
        };

        loadOlderBtn?.addEventListener('click', () => {
            if (!openConversationId || !oldestMessageId) return;
            const conversationId = openConversationId;
            av_whatsapp_rest(`conversations/${conversationId}/messages?before_id=${oldestMessageId}&limit=30`)
                .then(data => {
                    if (openConversationId !== conversationId) return;
                    const msgs = data.messages || [];
                    loadOlderBtn.classList.toggle('is-hidden', !data.has_more);
                    if (!msgs.length) return;

                    const before = messagesWrap.scrollHeight;
                    const holder = document.createElement('div');
                    holder.innerHTML = msgs.map(renderMessage).join('');
                    while (holder.firstChild) messagesListEl.insertBefore(holder.firstChild, messagesListEl.firstChild);
                    oldestMessageId = msgs[0].id;
                    messagesWrap.scrollTop = messagesWrap.scrollHeight - before;
                });
        });

        backBtn?.addEventListener('click', () => {
            root.classList.remove('is-chat-open');
        });

        // ── Polling inteligente: solo mientras la pestaña está visible, y sin
        // volver a descargar toda la conversación en cada ciclo — primero se
        // comprueba un marcador ligero (unread-count) y solo si cambió algo se
        // piden los datos concretos que hayan cambiado. ──
        const POLL_INTERVAL = 8000;
        let pollTimer = null;

        const pollTick = () => {
            av_whatsapp_rest('unread-count').then(data => {
                av_whatsapp_apply_badge(data.unread_conversations);

                if (data.last_activity_at && data.last_activity_at !== lastActivitySeen) {
                    lastActivitySeen = data.last_activity_at;
                    loadConversations(true);

                    if (openConversationId && newestMessageId) {
                        const conversationId = openConversationId;
                        av_whatsapp_rest(`conversations/${conversationId}/messages?since_id=${newestMessageId}`)
                            .then(d => {
                                if (openConversationId !== conversationId) return;
                                const nuevos = d.messages || [];
                                if (!nuevos.length) return;
                                messagesListEl.insertAdjacentHTML('beforeend', nuevos.map(renderMessage).join(''));
                                newestMessageId = nuevos[nuevos.length - 1].id;
                                scrollMessagesToBottom();
                                av_whatsapp_rest(`conversations/${conversationId}/read`, { method: 'POST' })
                                    .then(() => { clearRowBadge(conversationId); av_whatsapp_refresh_badge(); });
                            });
                    }
                }
            }).catch(() => {});
        };

        const startPolling = () => { if (!pollTimer) { pollTick(); pollTimer = setInterval(pollTick, POLL_INTERVAL); } };
        const stopPolling  = () => { clearInterval(pollTimer); pollTimer = null; };

        document.addEventListener('visibilitychange', () => {
            document.hidden ? stopPolling() : startPolling();
        });

        loadConversations(true);
        if (!document.hidden) startPolling();

        const preOpen = parseInt(root.dataset.openConversation, 10);
        if (preOpen) openConversation(preOpen);
    };

    // Botón flotante "volver arriba": aparece abajo a la derecha al hacer
    // scroll hacia abajo, en cualquier página con contenido largo.
    const av_back_to_top = () => {
        if (document.querySelector('.js-back-to-top')) return;

        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'c-back-to-top js-back-to-top';
        btn.setAttribute('aria-label', 'Volver arriba');
        btn.title = 'Volver arriba';
        btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="19" x2="12" y2="5"/><polyline points="5 12 12 5 19 12"/></svg>';
        document.body.appendChild(btn);

        const toggle = () => btn.classList.toggle('is-visible', window.scrollY > 400);
        toggle();
        window.addEventListener('scroll', toggle, { passive: true });

        btn.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    };

    const av_start_funcs = () => {

        av_reset_vars_css()

        av_back_to_top()

        av_call_fn('.js-gallery__wrapper-image', av_gallery_image)

        av_call_fn('.js-gallery__remove-image', av_gallery_remove_image)

        av_call_fn('.js-header__hamburguer', av_header_hamburguer)

        av_call_fn('.js-header__collapse', av_header_collapse)

        av_call_fn('.js-header-search', av_header_sat_search)

        av_call_fn('.js-dashboard-month-picker', av_dashboard_month_picker)

        av_call_fn('.js-dashboard-chart', av_dashboard_chart)

        av_call_fn('.js-generic-velo', av_generic_velo_close)

        av_call_fn('.js-slider', av_slider)

        av_call_fn('.js-c-contact__wrapper-image', av_contact_hover_image)

        av_call_fn('.js-split-text', av_split_text_anim)

        av_call_fn('.js-anim-image', av_image_anim)

        av_call_fn('.js-open-contact', av_open_contact)

        av_call_fn('.js-b-contact__close', av_close_contact)

        av_call_fn('.js-footer-map', av_footer_map)

        av_call_fn('.js-video__toggle-scroll', av_video_toggle)

        av_call_fn('.js-menu', av_menu_images)

        av_call_fn('.js-footer', av_footer_icons)

        av_call_fn('.c-menu__bg-wrapper-image', av_menu_order_images)

        av_call_fn('.js-hover', av_hover)

        av_call_fn('.js-hover-node', av_hover_node)

        av_call_fn('.js-single-cpt-themes__video-play', av_single_cpt_themes_video_play)

        av_call_fn('.js-sat-form__type-equipment', av_sat_form__equipment)

        av_call_fn('[name="estado"]', av_sat_form__repair_date)

        // av_call_fn('.js-get-client-list', av_get_client_list)

        av_call_fn('.js-list-cpt-sats__save-status', av_save_status)

        av_call_fn('.js-check-user', av_check_user)

        av_call_fn('.js-sat-client-picker__search', av_sat_client_picker)

        av_call_fn('.js-user-details', av_user_details)

        av_call_fn('.js-search-select', av_search_select)

        av_call_fn('.js-list-cpt-sats__select-status', av_enable_button_save_status)

        av_call_fn('.c-sat-form__form', av_check_form_changed)

        document.querySelectorAll('.js-repair-widget').forEach(av_repair_list)

        document.querySelectorAll('.js-repair-widget--servicios').forEach(av_repair_servicios_autocomplete)

        document.querySelectorAll('.js-repair-widget--articulos').forEach(av_repair_articulos_autocomplete)

        // Habilitar/deshabilitar botón Factura PDF según campo precio
        const invoiceBtn  = document.querySelector('.js-invoice-btn')
        const priceInput  = document.querySelector('[name="price"]')
        if (invoiceBtn && priceInput) {
            const toggleInvoiceBtn = () => {
                invoiceBtn.setAttribute('aria-disabled', priceInput.value.trim() ? 'false' : 'true')
            }
            priceInput.addEventListener('input', toggleInvoiceBtn)
            priceInput.addEventListener('change', toggleInvoiceBtn)
        }

        // La garantía también hay que elegirla explícitamente antes de generar
        // la factura (el select se deshabilita en los SATs que ya son garantía,
        // ahí no hace falta).
        if (invoiceBtn) {
            invoiceBtn.addEventListener('click', (e) => {
                const warrantySelect = document.querySelector('.js-sat-form__warranty-period')
                if (warrantySelect && !warrantySelect.disabled && !warrantySelect.value) {
                    e.preventDefault()
                    alert('Antes de generar la factura debes indicar si el SAT tiene garantía o no.')
                    warrantySelect.focus()
                }
            })
        }

        av_call_fn('.js-sat-form__warranty-modal', av_sat_form_warranty_modal)

        av_call_fn('.c-sat-form__form', av_sat_form_validate_reparado)

        av_call_fn('.js-sat-form__finalize-btn', av_sat_form_finalize_button_toggle)

        av_call_fn('.c-sat-form__form', av_sat_form_validate_finalizar)

        av_call_fn('.js-sat-form__anticipo', av_sat_form__anticipo_toggle)

        av_call_fn('.c-sat-form__form', av_sat_form_validate_anticipo)

        av_call_fn('.js-sat-form__signature-pad', av_sat_form_signature_pad)

        av_call_fn('.js-photo-field', av_sat_photo_fields)

        // Páginas sin formulario que solo muestran fotos (seguimiento del cliente)
        av_call_fn('.js-photo-zoom', av_photo_modal)

        av_call_fn('.js-remove-search-list-sats', av_remove_search_sat)

        av_call_fn('.js-filters-toggle', av_filters_toggle)

        av_call_fn('.js-usuarios-modal', av_usuarios_modal)

        av_call_fn('.js-servicios-modal', av_servicios_modal)

        av_call_fn('.js-devices-modal', av_devices_modal)

        av_call_fn('.js-device-categories-modal', av_device_categories_modal)

        av_call_fn('.js-device-categories-tbody', av_device_categories_drag)

        av_call_fn('.js-articulo-form', av_articulo_form)

        av_call_fn('.c-sat-form__accordion', av_accordion_collapse)

        av_call_fn('.js-articulo-stock-modal', av_articulo_stock_modal)

        av_call_fn('.js-articulos-filter-form', av_articulos_filter_async)

        av_call_fn('.js-sat-articulos-widget', av_sat_articulos_widget)

        av_call_fn('.js-pedido-form', av_pedido_client_picker)

        av_call_fn('.js-pedido-form', av_pedido_form_validate_delivered)

        av_call_fn('.js-pedido-lines-widget', av_pedido_lines_widget)

        av_call_fn('.js-sat-lines-widget', av_sat_lines_widget)

        av_call_fn('.js-pedidos-filter-form', av_pedidos_filter_async)
        av_call_fn('.js-pedido-status-filter', () => av_pill_tab_filter('.js-pedido-status-filter', '.js-pedidos-filter-form', '#f-filter'))

        av_call_fn('.js-presupuesto-form', av_presupuesto_client_picker)

        av_call_fn('.js-presupuesto-lines-widget', av_presupuesto_lines_widget)

        av_call_fn('.js-presupuestos-filter-form', av_presupuestos_filter_async)

        av_call_fn('.js-pedido-set-status', av_pedido_set_status_buttons)

        av_call_fn('.js-pedido-save-btn', av_pedido_dirty_save_button)


        av_call_fn('.js-sats-filter-form', av_sats_filter_async)

        av_call_fn('.js-clients-filter-form', av_clients_filter_async)
        av_call_fn('.js-client-type-filter', () => av_pill_tab_filter('.js-client-type-filter', '.js-clients-filter-form', '#f-tipo-cliente'))

        av_call_fn('.js-facturas-filter-form', av_facturas_filter_async)

        av_call_fn('.js-wa-secret-change', av_whatsapp_secret_toggle)

        av_call_fn('.js-whatsapp-test-connection', av_whatsapp_test_connection_btn)

        av_call_fn('.js-whatsapp-inbox', av_whatsapp_inbox)

        av_call_fn('.js-whatsapp-nav-badge', av_whatsapp_nav_badge_poll)

        av_call_fn('.js-whatsapp-open-btn', av_whatsapp_open_button)

        av_call_fn('.js-whatsapp-sat-card', av_whatsapp_sat_card)

        av_call_fn('.js-filter-all', av_filter_all)

        av_call_fn('.js-client-form__type-client', av_client_type_toggle)

        av_call_fn('.js-client-form__phone2-toggle', av_client_phone2_toggle)

        av_call_fn('.js-compra-items', av_compra_items_widget)

        av_call_fn('.js-client-modal', av_client_modal)

        av_global_scroll()

        av_smooth_scroller_init()

        // --- ON RESIZE WINDOW EVENT --------------------------------
        window.removeEventListener('resize', windowResize)
        const windowResize = window.addEventListener('resize', () => {

            w = window,
            d = document,
            e = d.documentElement,
            g = document.body,
            x = w.innerWidth || e.clientWidth || (g && g.clientWidth),
            y = w.innerHeight|| e.clientHeight || (g && g.clientHeight);

        })
        // END ON RESIZE WINDOW EVENT --------------------------------


        // $(window).trigger('resize');
        // window.dispatchEvent(new Event('resize'));
        

        // --- ON SCROLL WINDOW EVENT --------------------------------
        // $(window).off('scroll');
        // $(window).on('scroll', av_scroll);
        // av_scroll();

        
        
        // END ON RESIZE WINDOW EVENT --------------------------------

    }

    // --- ON LOAD --------------------------------------
    document.addEventListener('DOMContentLoaded', () => {
        av_remove_loader()

        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('.wpcf7 form');
            if (form) {
                form.setAttribute('novalidate', 'novalidate');
            }
        });
    })
    // END ON LOAD --------------------------------------

