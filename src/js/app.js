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
            x = w.innerWidth || e.clientWidth || g.clientWidth,
            y = w.innerHeight|| e.clientHeight|| g.clientHeight;

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
    const AV_STATUS_CHEVRON_LIGHT = "url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='%23ffffff' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E\")"

    const av_enable_button_save_status = () => {

        const nodeSelect = document.querySelectorAll('.js-list-cpt-sats__select-status')
        nodeSelect.forEach(select => {

            // Pinta el propio select como una pastilla de color (antes se
            // coloreaba toda la celda, mucho más recargado visualmente).
            const aplicarColor = () => {
                const { bgColor, textColor } = av_change_color_status(select.value)
                select.style.backgroundColor = bgColor;
                select.style.color = textColor;
                select.style.backgroundImage = select.value === 'finalizado' ? AV_STATUS_CHEVRON_LIGHT : AV_STATUS_CHEVRON_DARK;
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
        // "Finalizado" es la única con fondo sólido: marca visualmente que es
        // el estado final/cerrado.
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
            case 'finalizado':
                bgColor = '#16a34a'; textColor = '#fff';
                break;
        }

        return {bgColor, textColor}
    }

   const av_save_status = () => {
        let timeoutId = null;

        // ── Banner de datos faltantes (reparación+piezas / precio / tipo de pago) ──
        const banner        = document.querySelector('.js-list-cpt-sats__finalize-banner');
        const bannerTitle   = banner?.querySelector('.js-list-cpt-sats__finalize-banner-title');
        const bannerText    = banner?.querySelector('.js-list-cpt-sats__finalize-banner-text');
        const bannerError   = banner?.querySelector('.js-list-cpt-sats__finalize-banner-error');
        const bannerRepairField  = banner?.querySelector('.js-list-cpt-sats__finalize-banner-field-repair');
        const bannerPartsField   = banner?.querySelector('.js-list-cpt-sats__finalize-banner-field-parts');
        const bannerRepairHidden = banner?.querySelector('.js-list-cpt-sats__finalize-banner-repair-hidden');
        const bannerPartsHidden  = banner?.querySelector('.js-list-cpt-sats__finalize-banner-parts-hidden');
        const bannerPriceField   = banner?.querySelector('.js-list-cpt-sats__finalize-banner-field-price');
        const bannerPriceInput   = banner?.querySelector('.js-list-cpt-sats__finalize-banner-price-input');
        const bannerPaymentField = banner?.querySelector('.js-list-cpt-sats__finalize-banner-field-payment');
        const bannerPaymentSelect = banner?.querySelector('.js-list-cpt-sats__finalize-banner-payment-select');
        const bannerCancelBtn   = banner?.querySelector('.js-list-cpt-sats__finalize-banner-cancel');
        const bannerConfirmBtn  = banner?.querySelector('.js-list-cpt-sats__finalize-banner-confirm');

        // Items ya escritos en un widget de reparación/piezas del banner
        // (mismo formato {text, price} que usa el detalle del SAT).
        const getBannerWidgetItems = (hidden) => {
            if (!hidden) return [];
            try {
                const items = JSON.parse(hidden.value);
                return Array.isArray(items) ? items.filter(it => (it.text || '').trim()) : [];
            } catch (e) { return []; }
        };

        // Vacía los dos widgets del banner y avisa al widget para que se
        // vuelva a pintar (así no arrastra líneas del SAT anterior).
        const resetBannerRepairWidgets = () => {
            [bannerRepairHidden, bannerPartsHidden].forEach(hidden => {
                if (!hidden) return;
                hidden.value = '[]';
                hidden.dispatchEvent(new CustomEvent('av:repair-reset', { bubbles: false }));
            });
        };

        // ── Modal "¿marcar la entrega como firmada?" al finalizar ───────────
        const deliveryModal    = document.querySelector('.js-list-cpt-sats__delivery-modal');
        const deliveryModalYes = deliveryModal?.querySelector('.js-list-cpt-sats__delivery-modal-yes');
        const deliveryModalNo  = deliveryModal?.querySelector('.js-list-cpt-sats__delivery-modal-no');
        let deliveryPendingArgs = null;

        // Antes de guardar de verdad: si el SAT pasa a finalizado justo ahora
        // (no lo estaba ya) y todavía no tiene la entrega firmada, se
        // pregunta una vez. No es obligatorio responder que sí.
        const maybeAskDeliveryThenSend = (ctx, precioFinal, tipoPago, repairData) => {
            const esEstadoDeEntrega = ctx.statusValue === 'finalizado' || ctx.statusValue === 'no-reparado';
            const yaEstabaEnEsteEstado = ctx.wrapper.dataset.savedStatus === ctx.statusValue;
            const yaFirmada = ctx.wrapper.dataset.deliverySigned === '1';

            if (!deliveryModal || !esEstadoDeEntrega || yaEstabaEnEsteEstado || yaFirmada) {
                sendStatusUpdate(ctx, precioFinal, tipoPago, repairData);
                return;
            }

            deliveryPendingArgs = { ctx, precioFinal, tipoPago, repairData };
            deliveryModal.classList.add('is-active');
        };

        deliveryModalYes?.addEventListener('click', () => {
            if (!deliveryPendingArgs) return;
            const { ctx, precioFinal, tipoPago, repairData } = deliveryPendingArgs;
            deliveryPendingArgs = null;
            deliveryModal.classList.remove('is-active');
            sendStatusUpdate(ctx, precioFinal, tipoPago, repairData, true);
        });

        deliveryModalNo?.addEventListener('click', () => {
            if (!deliveryPendingArgs) return;
            const { ctx, precioFinal, tipoPago, repairData } = deliveryPendingArgs;
            deliveryPendingArgs = null;
            deliveryModal.classList.remove('is-active');
            sendStatusUpdate(ctx, precioFinal, tipoPago, repairData, false);
        });

        let pendingContext = null;

        const closeBanner = () => {
            banner.classList.remove('is-active');
            if (bannerError) bannerError.textContent = '';
            resetBannerRepairWidgets();
            if (bannerPriceInput) bannerPriceInput.value = '';
            if (bannerPaymentSelect) bannerPaymentSelect.value = '';
            pendingContext = null;
        };

        const openBanner = (ctx) => {
            pendingContext = ctx;

            const missing = [];
            if (ctx.needsRepair)  missing.push('la reparación o piezas pedidas');
            if (ctx.needsPrice)   missing.push('el precio');
            if (ctx.needsPayment) missing.push('el tipo de pago');

            const missingText = missing.length > 1
                ? missing.slice(0, -1).join(', ') + ' y ' + missing[missing.length - 1]
                : missing[0];

            // El mismo banner sirve para "reparado" (falta la reparación) y para
            // "finalizado" (faltan reparación/piezas, precio y/o tipo de pago).
            const accion = ctx.statusValue === 'reparado' ? 'marcar el SAT como reparado' : 'finalizar el SAT';

            bannerTitle.textContent = 'Faltan datos para ' + accion;
            bannerText.textContent  = 'Antes de ' + accion + ' debes indicar ' + missingText + '.';
            bannerRepairField.style.display  = ctx.needsRepair ? '' : 'none';
            bannerPartsField.style.display   = ctx.needsRepair ? '' : 'none';
            bannerPriceField.style.display   = ctx.needsPrice ? '' : 'none';
            bannerPaymentField.style.display = ctx.needsPayment ? '' : 'none';
            bannerError.textContent = '';
            resetBannerRepairWidgets();
            bannerPriceInput.value = '';
            bannerPaymentSelect.value = '';

            banner.classList.add('is-active');

            if (ctx.needsRepair) {
                banner.querySelector('.js-repair-input')?.focus();
            } else if (ctx.needsPrice) {
                bannerPriceInput.focus();
            } else {
                bannerPaymentSelect.focus();
            }
        };

        const sendStatusUpdate = (ctx, precioFinal, tipoPago, repairData, entregaFirmada = null) => {
            const { satId, statusValue, wrapper, select, nodeSaveStatus, nodePrice, saveBlock } = ctx;

            const formData = new FormData();
            formData.append('action', 'av_ajax_save_sat_status');
            formData.append('sat-id', satId);
            formData.append('status', statusValue);
            if (precioFinal !== null) formData.append('precio-final', precioFinal);
            if (tipoPago !== null) formData.append('tipo-pago', tipoPago);
            if (repairData) {
                formData.append('reparacion-json', repairData.repairJson);
                formData.append('piezas-json', repairData.partsJson);
            }
            if (entregaFirmada !== null) {
                formData.append('entrega-firmada', entregaFirmada ? '1' : '');
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

                if(price !== null) nodePrice.textContent = price + ' €';
                if(payment) {
                    wrapper.dataset.payment = payment;
                    nodePrice.title = payment;
                }
                if(repairData) wrapper.dataset.repair = '1';
                wrapper.dataset.savedStatus = statusValue;

                // La firma de la entrega solo se conserva en un estado de
                // entrega; al salir hacia cualquier otro estado el servidor
                // ya la resetea sola, así que aquí solo hay que reflejarlo.
                const esEstadoDeEntrega = statusValue === 'finalizado' || statusValue === 'no-reparado';
                const entregaFinal = entregaFirmada !== null ? entregaFirmada : ( esEstadoDeEntrega ? null : false );
                if (entregaFinal !== null) {
                    wrapper.dataset.deliverySigned = entregaFinal ? '1' : '0';
                    const deliveryBadge = wrapper.closest('tr')?.querySelector('.c-list-cpt-sats__delivery-badge');
                    if (deliveryBadge) {
                        deliveryBadge.classList.toggle('is-signed', entregaFinal);
                        deliveryBadge.classList.toggle('is-pending', !entregaFinal);
                        deliveryBadge.textContent = entregaFinal ? 'Firmada' : 'Pendiente';
                        deliveryBadge.title = entregaFinal ? 'Entrega firmada' : 'Pendiente firma entrega';
                    }
                }

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

                let precioFinal = null;
                let tipoPago = null;
                let repairData = null;

                if (pendingContext.needsRepair) {
                    const repairItems = getBannerWidgetItems(bannerRepairHidden);
                    const partsItems  = getBannerWidgetItems(bannerPartsHidden);
                    if (repairItems.length === 0 && partsItems.length === 0) {
                        bannerError.textContent = 'Rellena el campo de Reparación o el de Piezas pedidas.';
                        return;
                    }
                    repairData = {
                        repairJson: JSON.stringify(repairItems),
                        partsJson: JSON.stringify(partsItems),
                    };
                }

                if (pendingContext.needsPrice) {
                    // El precio se calcula solo sumando las líneas de arriba
                    // (igual que en el detalle del SAT); aquí solo se revisa.
                    const raw = (bannerPriceInput.value || '').replace(',', '.');
                    const value = parseFloat(raw);
                    if (!raw || isNaN(value) || value <= 0) {
                        bannerError.textContent = 'Añade al menos una línea con precio en Reparación o Piezas pedidas.';
                        return;
                    }
                    precioFinal = value;
                }

                if (pendingContext.needsPayment) {
                    if (!bannerPaymentSelect.value) {
                        bannerError.textContent = 'Selecciona el tipo de pago.';
                        return;
                    }
                    tipoPago = bannerPaymentSelect.value;
                }

                const ctx = pendingContext;
                closeBanner();
                maybeAskDeliveryThenSend(ctx, precioFinal, tipoPago, repairData);
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

                    let priceFormatted = nodePrice.textContent
                        .replace(/[^\d.,-]/g, '')   // quitar todo lo que no sea número, coma, punto o -
                        .replace(',', '.');        // convertir coma a punto

                    let numero = parseFloat(priceFormatted);

                    if (!isNaN(numero)) {
                        numero = Number(numero.toFixed(2)); // asegurar 2 decimales numéricos
                    }

                    const currentPayment = (wrapper.dataset.payment || '').toLowerCase();
                    const isWarranty = wrapper.dataset.warranty === '1';
                    const hasRepair  = wrapper.dataset.repair === '1';

                    // Igual que en el detalle del SAT: no se puede marcar como reparado NI
                    // finalizar sin indicar qué se ha reparado o qué piezas se han pedido.
                    const needsRepair  = ( statusValue === 'reparado' || statusValue === 'finalizado' ) && !hasRepair;
                    const needsPrice   = !isWarranty && statusValue === 'finalizado' && (isNaN(numero) || numero === 0);
                    const needsPayment = !isWarranty && statusValue === 'finalizado' && currentPayment !== 'tarjeta' && currentPayment !== 'efectivo';

                    const ctx = { satId, statusValue, wrapper, select, nodeSaveStatus, nodePrice, saveBlock, needsRepair, needsPrice, needsPayment };

                    if (needsRepair || needsPrice || needsPayment) {
                        openBanner(ctx);
                        return;
                    }

                    maybeAskDeliveryThenSend(ctx, null, null, null);
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

        const repairHidden = formulario.querySelector('[name="repair"].js-repair-hidden');
        const partsHidden  = formulario.querySelector('[name="ordered-parts"].js-repair-hidden');
        const repairItems  = parseItems(repairHidden);
        const partsItems   = parseItems(partsHidden);

        if (repairItems.length === 0 && partsItems.length === 0) {
            alert(`Para ${accion} el SAT debes rellenar el campo de Reparación o el de Piezas pedidas.`);
            const repairInput = formulario.querySelector('.js-repair-input');
            if (repairInput) repairInput.focus();
            return { ok: false, hasPositivePrice: false };
        }

        const priceInput  = formulario.querySelector('[name="price"]');
        const manualPrice = (priceInput?.value || '').trim();
        const manualValue = parseFloat(manualPrice.replace(',', '.'));

        const itemsHavePrice = [...repairItems, ...partsItems].some(it => {
            const p = parseFloat(String(it.price || '').replace(',', '.'));
            return !isNaN(p) && p > 0;
        });

        if (manualPrice === '' && !itemsHavePrice) {
            const confirmarSinCoste = confirm(`No se ha indicado ningún precio (ni en Coste Final ni en las líneas de reparación/piezas). ¿Quieres ${accion} este SAT con precio 0€?`);
            if (!confirmarSinCoste) {
                priceInput?.focus();
                return { ok: false, hasPositivePrice: false };
            }
            if (priceInput) priceInput.value = '0';
            return { ok: true, hasPositivePrice: false };
        }

        return { ok: true, hasPositivePrice: itemsHavePrice || (!isNaN(manualValue) && manualValue > 0) };
    };

    // La firma de la entrega solo tiene sentido con el SAT finalizado: el
    // checkbox se habilita/deshabilita al vuelo según el select de Estado,
    // sin esperar a guardar (coherente con lo que hará el servidor).
    const av_sat_form_delivery_signed_toggle = () => {

        const formulario = document.querySelector('.c-sat-form__form');
        const checkbox    = formulario?.querySelector('.js-sat-form__delivery-signed');
        const estadoSelect = formulario?.querySelector('[name="estado"]');
        if (!formulario || !checkbox || !estadoSelect) return;

        const sync = () => {
            const esEstadoDeEntrega = estadoSelect.value === 'finalizado' || estadoSelect.value === 'no-reparado';
            checkbox.disabled = !esEstadoDeEntrega;
            checkbox.title = esEstadoDeEntrega ? '' : 'Solo se puede marcar cuando el SAT está finalizado o no reparado';
            // Al salir de un estado de entrega se desmarca visualmente: al
            // guardar, el servidor la resetea igualmente (como si el cliente
            // hubiera vuelto a dejar el equipo).
            if (!esEstadoDeEntrega) checkbox.checked = false;
        };

        estadoSelect.addEventListener('change', sync);
    };

    const av_sat_form_validate_reparado = () => {

        const formulario = document.querySelector('.c-sat-form__form');
        if (!formulario) return;

        formulario.addEventListener('submit', (e) => {

            const estadoSelect = formulario.querySelector('[name="estado"]');
            if (!estadoSelect || estadoSelect.value !== 'reparado') return;

            if (!av_sat_form_check_repair_and_price(formulario, 'marcar como reparado').ok) {
                e.preventDefault();
            }
        });
    }

    const av_sat_form_validate_finalizado = () => {

        const formulario = document.querySelector('.c-sat-form__form');
        if (!formulario) return;

        // Estado guardado al cargar la página: sirve para saber si este envío
        // es justo el que lleva el SAT a un estado de entrega (transición
        // real desde otro estado distinto), no un simple resave de un SAT
        // que ya estaba en ese mismo estado.
        const estadoSelectInicial = formulario.querySelector('[name="estado"]');
        const estadoInicial = estadoSelectInicial ? estadoSelectInicial.value : null;

        const modal            = formulario.querySelector('.js-sat-form__delivery-modal');
        const modalYes         = modal?.querySelector('.js-sat-form__delivery-modal-yes');
        const modalNo          = modal?.querySelector('.js-sat-form__delivery-modal-no');
        const deliveryCheckbox = formulario.querySelector('.js-sat-form__delivery-signed');

        let deliveryPromptRespondido = false;

        // Al entrar en un estado de entrega (finalizado o no reparado; solo si
        // es una transición real, no un resave) y si todavía no está marcada,
        // se pregunta una vez si se firma la entrega ahora mismo. No es
        // obligatorio responder que sí: cualquiera de las dos opciones deja
        // continuar el guardado con normalidad.
        const maybePreguntarFirmaEntrega = (e) => {
            if (deliveryPromptRespondido || !modal) return;
            const estadoActual = formulario.querySelector('[name="estado"]')?.value;
            if (estadoInicial === estadoActual) return;
            if (deliveryCheckbox && deliveryCheckbox.checked) return;

            e.preventDefault();
            modal.classList.add('is-active');
        };

        modalYes?.addEventListener('click', () => {
            deliveryPromptRespondido = true;
            if (deliveryCheckbox) {
                // En este punto el SAT ya se está guardando como "finalizado",
                // así que aunque el checkbox siguiera deshabilitado (el
                // listener del select no hubiera llegado a activarlo) se
                // habilita aquí para que su valor sí viaje en el envío.
                deliveryCheckbox.disabled = false;
                deliveryCheckbox.checked = true;
            }
            modal.classList.remove('is-active');
            formulario.requestSubmit();
        });

        modalNo?.addEventListener('click', () => {
            deliveryPromptRespondido = true;
            modal.classList.remove('is-active');
            formulario.requestSubmit();
        });

        formulario.addEventListener('submit', (e) => {

            const estadoSelect = formulario.querySelector('[name="estado"]');
            if (!estadoSelect) return;

            // "No reparado" no exige reparación/precio/pago (no hay nada que
            // cobrar): solo se pregunta por la firma de la entrega.
            if (estadoSelect.value === 'no-reparado') {
                maybePreguntarFirmaEntrega(e);
                return;
            }

            if (estadoSelect.value !== 'finalizado') return;

            // Los SATs de garantía no llevan precio ni tipo de pago
            const isWarranty = formulario.querySelector('[name="is-warranty"]');
            if (isWarranty && isWarranty.value === '1') return;

            // Hay que elegir explícitamente si hay garantía o no (el
            // placeholder "Seleccionar..." no es una opción válida).
            const warrantySelect = formulario.querySelector('[name="warranty-period"]');
            if (warrantySelect && !warrantySelect.value) {
                e.preventDefault();
                alert('Antes de finalizar el SAT debes indicar si tiene garantía o no.');
                warrantySelect.focus();
                return;
            }

            // El precio ya queda resuelto aquí (manual, en líneas, o 0€
            // confirmado) — no hace falta volver a exigir Coste Final aparte.
            const check = av_sat_form_check_repair_and_price(formulario, 'finalizar');
            if (!check.ok) {
                e.preventDefault();
                return;
            }

            // A precio 0€ no tiene sentido pedir tipo de pago (no hay cobro).
            if (!check.hasPositivePrice) {
                maybePreguntarFirmaEntrega(e);
                return;
            }

            const paymentSelect = formulario.querySelector('[name="price-description"]');
            if (!paymentSelect || !paymentSelect.value) {
                e.preventDefault();
                alert('Antes de finalizar el SAT debes indicar el tipo de pago.');
                paymentSelect?.focus();
                return;
            }

            maybePreguntarFirmaEntrega(e);
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

        const title       = modal.querySelector('.js-servicios-modal-title');
        const submitBtn   = modal.querySelector('.js-servicios-submit');
        const fieldId     = modal.querySelector('.js-servicios-field-id');
        const fieldTitulo = modal.querySelector('.js-servicios-field-titulo');
        const fieldPrecio = modal.querySelector('.js-servicios-field-precio');
        const form        = modal.querySelector('.js-servicios-form');

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
            title.textContent = 'Nuevo servicio';
            submitBtn.textContent = 'Crear servicio';
        };

        const fillForEdit = (btn) => {
            const data = btn.dataset;

            fieldId.value     = data.id;
            fieldTitulo.value = data.titulo;
            fieldPrecio.value = data.precio;

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

            const hidden = form.querySelector('.js-client-form__type-client')
            const btns   = form.querySelectorAll('.js-client-type-btn')
            if (!hidden || !btns.length) return

            btns.forEach(btn => {
                btn.addEventListener('click', () => {
                    if (btn.classList.contains('is-active')) return

                    btns.forEach(b => b.classList.remove('is-active'))
                    btn.classList.add('is-active')
                    hidden.value = btn.dataset.value
                })
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

        av_call_fn('.js-sat-form__delivery-signed', av_sat_form_delivery_signed_toggle)

        av_call_fn('.c-sat-form__form', av_sat_form_validate_reparado)

        av_call_fn('.c-sat-form__form', av_sat_form_validate_finalizado)

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

        av_call_fn('.js-sats-filter-form', av_sats_filter_async)

        av_call_fn('.js-clients-filter-form', av_clients_filter_async)

        av_call_fn('.js-facturas-filter-form', av_facturas_filter_async)

        av_call_fn('.js-wa-secret-change', av_whatsapp_secret_toggle)

        av_call_fn('.js-whatsapp-test-connection', av_whatsapp_test_connection_btn)

        av_call_fn('.js-whatsapp-inbox', av_whatsapp_inbox)

        av_call_fn('.js-whatsapp-nav-badge', av_whatsapp_nav_badge_poll)

        av_call_fn('.js-whatsapp-open-btn', av_whatsapp_open_button)

        av_call_fn('.js-whatsapp-sat-card', av_whatsapp_sat_card)

        av_call_fn('.js-filter-all', av_filter_all)

        av_call_fn('.js-client-form__type-client', av_client_type_toggle)

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
            x = w.innerWidth || e.clientWidth || g.clientWidth,
            y = w.innerHeight|| e.clientHeight|| g.clientHeight;

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

