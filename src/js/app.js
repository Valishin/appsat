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
import { jsPDF } from 'jspdf';

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

    const av_enable_button_save_status = () => {

        const nodeSelect = document.querySelectorAll('.js-list-cpt-sats__select-status')
        nodeSelect.forEach(select => {
        
            const tdPadre = select.closest('td');

            // obtener el valor seleccionado
            const valorCargado = av_change_color_status(select.value)                                               
            // aplicar colores al select
            tdPadre.style.backgroundColor = valorCargado.bgColor;
            tdPadre.style.color = valorCargado.textColor;

            select.addEventListener('change', () => {                

                const wrapper = select.closest('.js-list-cpt-sats__wrapper-select-status');
                if (!wrapper) return;
                const nodeSaveStatus = wrapper.querySelector('.js-list-cpt-sats__save-status');
                nodeSaveStatus.classList.add('is-active');  

                // obtener el valor seleccionado
                const valor = av_change_color_status(select.value)                                               
                // aplicar colores al select
                tdPadre.style.backgroundColor = valor.bgColor;
                tdPadre.style.color = valor.textColor;
                           
            });
        })
    }

    const av_change_color_status = (estado) => {

        // switch para asignar colores
        let bgColor = '#fff'; // default
        let textColor = '#000'; 

        switch(estado) {
            case 'diagnosticar':
                bgColor = '#FFF176'; textColor = '#000';
                break;
            case 'cliente-espera':
                bgColor = '#FFB74D'; textColor = '#000';
                break;
            case 'pieza':
                bgColor = '#CE93D8'; textColor = '#000';
                break;
            case 'otro-sat':
                bgColor = '#731087'; textColor = '#fff';
                break;
            case 'reparar':
                bgColor = '#64B5F6'; textColor = '#000';
                break;
            case 'reparado':
                bgColor = '#81C784'; textColor = '#fff';
                break;
            case 'no-reparado':
                bgColor = '#E57373'; textColor = '#fff';
                break;
            case 'garantia':
                bgColor = '#B0BEC5'; textColor = '#000';
                break;            
            case 'finalizado':
                bgColor = '#388E3C'; textColor = '#000';
                break;
        }

        return {bgColor, textColor}
    }

   const av_save_status = () => {
        let timeoutId = null;

        // ── Banner de datos faltantes (reparación / precio / tipo de pago) ──
        const banner        = document.querySelector('.js-list-cpt-sats__finalize-banner');
        const bannerTitle   = banner?.querySelector('.js-list-cpt-sats__finalize-banner-title');
        const bannerText    = banner?.querySelector('.js-list-cpt-sats__finalize-banner-text');
        const bannerError   = banner?.querySelector('.js-list-cpt-sats__finalize-banner-error');
        const bannerRepairField  = banner?.querySelector('.js-list-cpt-sats__finalize-banner-field-repair');
        const bannerRepairInput  = banner?.querySelector('.js-list-cpt-sats__finalize-banner-repair-input');
        const bannerPriceField   = banner?.querySelector('.js-list-cpt-sats__finalize-banner-field-price');
        const bannerPriceInput   = banner?.querySelector('.js-list-cpt-sats__finalize-banner-price-input');
        const bannerPaymentField = banner?.querySelector('.js-list-cpt-sats__finalize-banner-field-payment');
        const bannerPaymentSelect = banner?.querySelector('.js-list-cpt-sats__finalize-banner-payment-select');
        const bannerCancelBtn   = banner?.querySelector('.js-list-cpt-sats__finalize-banner-cancel');
        const bannerConfirmBtn  = banner?.querySelector('.js-list-cpt-sats__finalize-banner-confirm');

        let pendingContext = null;

        const closeBanner = () => {
            banner.classList.remove('is-active');
            if (bannerError) bannerError.textContent = '';
            if (bannerRepairInput) bannerRepairInput.value = '';
            if (bannerPriceInput) bannerPriceInput.value = '';
            if (bannerPaymentSelect) bannerPaymentSelect.value = '';
            pendingContext = null;
        };

        const openBanner = (ctx) => {
            pendingContext = ctx;

            const missing = [];
            if (ctx.needsRepair)  missing.push('la reparación realizada');
            if (ctx.needsPrice)   missing.push('el precio');
            if (ctx.needsPayment) missing.push('el tipo de pago');

            const missingText = missing.length > 1
                ? missing.slice(0, -1).join(', ') + ' y ' + missing[missing.length - 1]
                : missing[0];

            // El mismo banner sirve para "reparado" (falta la reparación) y para
            // "finalizado" (faltan precio y/o tipo de pago).
            const accion = ctx.statusValue === 'reparado' ? 'marcar el SAT como reparado' : 'finalizar el SAT';

            bannerTitle.textContent = 'Faltan datos para ' + accion;
            bannerText.textContent  = 'Antes de ' + accion + ' debes indicar ' + missingText + '.';
            bannerRepairField.style.display  = ctx.needsRepair ? '' : 'none';
            bannerPriceField.style.display   = ctx.needsPrice ? '' : 'none';
            bannerPaymentField.style.display = ctx.needsPayment ? '' : 'none';
            bannerError.textContent = '';
            bannerRepairInput.value = '';
            bannerPriceInput.value = '';
            bannerPaymentSelect.value = '';

            banner.classList.add('is-active');

            if (ctx.needsRepair) {
                bannerRepairInput.focus();
            } else if (ctx.needsPrice) {
                bannerPriceInput.focus();
            } else {
                bannerPaymentSelect.focus();
            }
        };

        const sendStatusUpdate = (ctx, precioFinal, tipoPago, reparacion) => {
            const { satId, statusValue, wrapper, select, nodeSaveStatus, nodePrice, saveBlock } = ctx;

            const formData = new FormData();
            formData.append('action', 'av_ajax_save_sat_status');
            formData.append('sat-id', satId);
            formData.append('status', statusValue);
            if (precioFinal !== null) formData.append('precio-final', precioFinal);
            if (tipoPago !== null) formData.append('tipo-pago', tipoPago);
            if (reparacion) formData.append('reparacion', reparacion);

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
                if(reparacion) wrapper.dataset.repair = '1';
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

                let precioFinal = null;
                let tipoPago = null;
                let reparacion = null;

                if (pendingContext.needsRepair) {
                    const repairValue = (bannerRepairInput.value || '').trim();
                    if (!repairValue) {
                        bannerError.textContent = 'Describe la reparación realizada.';
                        return;
                    }
                    reparacion = repairValue;
                }

                if (pendingContext.needsPrice) {
                    const raw = (bannerPriceInput.value || '').replace(',', '.');
                    const value = parseFloat(raw);
                    if (!raw || isNaN(value) || value <= 0) {
                        bannerError.textContent = 'Introduce un precio válido.';
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
                sendStatusUpdate(ctx, precioFinal, tipoPago, reparacion);
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

                    // Igual que en el detalle del SAT: no se puede marcar como reparado sin
                    // indicar qué se ha reparado.
                    const needsRepair  = statusValue === 'reparado' && !hasRepair;
                    const needsPrice   = !isWarranty && statusValue === 'finalizado' && (isNaN(numero) || numero === 0);
                    const needsPayment = !isWarranty && statusValue === 'finalizado' && currentPayment !== 'tarjeta' && currentPayment !== 'efectivo';

                    const ctx = { satId, statusValue, wrapper, select, nodeSaveStatus, nodePrice, saveBlock, needsRepair, needsPrice, needsPayment };

                    if (needsRepair || needsPrice || needsPayment) {
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
                            <span class="c-sat-client-picker__result-name">${escapeHtml(client.name)}</span>
                            <span class="c-sat-client-picker__result-meta">${escapeHtml(client.dni)}${client.phone ? ' · ' + escapeHtml(client.phone) : ''}</span>
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
            if (saveBtn) {
                saveBtn.disabled = true;
                saveBtn.title = 'Modifica algún campo para poder guardar';
            }
            cancelBtn?.classList.add('is-hidden');
        };

        formulario.addEventListener('input', setDirty);

        cancelBtn?.addEventListener('click', cancelChanges);

        window.addEventListener('beforeunload', (e) => {
            if (cambios) {
                e.preventDefault();
                e.returnValue = '';
            }
        });

        formulario.addEventListener('submit', () => {
            cambios = false;
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

        input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') { e.preventDefault(); addItem(); }
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

    const av_sat_form_validate_reparado = () => {

        const formulario = document.querySelector('.c-sat-form__form');
        if (!formulario) return;

        formulario.addEventListener('submit', (e) => {

            const estadoSelect = formulario.querySelector('[name="estado"]');
            if (!estadoSelect || estadoSelect.value !== 'reparado') return;

            const repairHidden = formulario.querySelector('.js-repair-hidden');
            let repairIsEmpty = true;
            if (repairHidden) {
                try {
                    const rItems = JSON.parse(repairHidden.value);
                    repairIsEmpty = !Array.isArray(rItems) || rItems.length === 0;
                } catch (e) {
                    repairIsEmpty = !repairHidden.value.trim();
                }
            }
            if (repairIsEmpty) {
                e.preventDefault();
                alert('Para marcar el SAT como reparado debes rellenar el campo de Reparación.');
                const repairInput = formulario.querySelector('.js-repair-input');
                if (repairInput) repairInput.focus();
                return;
            }

            const priceInput = formulario.querySelector('[name="price"]');
            if (priceInput && priceInput.value.trim() === '') {
                e.preventDefault();
                alert('Para marcar el SAT como reparado debes indicar el Coste Final.');
                priceInput.focus();
            }
        });
    }

    const av_sat_form_validate_finalizado = () => {

        const formulario = document.querySelector('.c-sat-form__form');
        if (!formulario) return;

        formulario.addEventListener('submit', (e) => {

            const estadoSelect = formulario.querySelector('[name="estado"]');
            if (!estadoSelect || estadoSelect.value !== 'finalizado') return;

            // Los SATs de garantía no llevan precio ni tipo de pago
            const isWarranty = formulario.querySelector('[name="is-warranty"]');
            if (isWarranty && isWarranty.value === '1') return;

            const priceInput   = formulario.querySelector('[name="price"]');
            const paymentSelect = formulario.querySelector('[name="price-description"]');

            const priceValue = parseFloat((priceInput?.value || '').replace(',', '.'));

            if (!priceInput || isNaN(priceValue) || priceValue <= 0) {
                e.preventDefault();
                alert('Antes de finalizar el SAT debes indicar el precio.');
                priceInput?.focus();
                return;
            }

            if (!paymentSelect || !paymentSelect.value) {
                e.preventDefault();
                alert('Antes de finalizar el SAT debes indicar el tipo de pago.');
                paymentSelect?.focus();
                return;
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

    const buildSatPdf = async () => {
            const form = document.querySelector('.c-sat-form__form');

            const getVal  = (name) => form.querySelector(`[name="${name}"]`)?.value || '';
            const getSel  = (name) => {
                const el = form.querySelector(`[name="${name}"]`);
                return el?.options[el.selectedIndex]?.text || '';
            };

            const satId          = document.querySelector('#sat-id-visible')?.value || '';
            const client         = getVal('client-name');
            const dni            = form.querySelector('#dni')?.value || '';
            const phone          = getVal('client-phone');
            const attended       = getSel('attended');
            const equipment      = getSel('type-equipment');
            const nameOther      = getVal('name-other');
            const model          = getVal('model');
            const serial         = getVal('serial');
            const accesories     = [...form.querySelectorAll('[name="accesories[]"] option:checked')].map(o => o.text).join(', ');
            const otherAcces     = getVal('other-accesories');
            const physCond       = getVal('physical-condition');
            const otherEquip     = getVal('other-equipment');
            const incident       = getVal('incident');
            const repair         = getVal('repair');
            const orderedParts   = getVal('ordered-parts');
            const price          = getVal('price');
            const repairDate     = getVal('repair-date');
            const sigImg         = document.querySelector('.c-sat-form__signature-img');

            const equipLabel = (equipment === 'Otro' && nameOther) ? `Otro (${nameOther})` : equipment;

            const pdf    = new jsPDF('p', 'mm', 'a4');
            const pageW  = pdf.internal.pageSize.getWidth();
            const pageH  = pdf.internal.pageSize.getHeight();
            const margin = 15;
            const col    = pageW - margin * 2;
            let y        = margin;

            // ── Pre-calcular bloque fijo inferior ──
            const legalSections = [
                {
                    title: 'PROTECCIÓN DE DATOS',
                    body:  'En cumplimiento del RGPD (UE) 2016/679 y la LOPDGDD 3/2018, le informamos que los datos personales recogidos serán tratados por iCoreByte SAT, NIF 49298273G, con la finalidad de gestionar la prestación del servicio técnico solicitado. Los datos se conservarán durante el tiempo necesario para la relación contractual y los plazos legales aplicables. Puede ejercer sus derechos de acceso, rectificación, supresión, portabilidad y oposición dirigiéndose a Carretera de Palamós 57, local · 17220 Sant Feliu de Guixols, Girona. Más información en nuestra política de privacidad disponible en icorebyte.com.'
                },
                {
                    title: 'CONDICIONES DEL SERVICIO',
                    body:  'La presente orden de reparación autoriza al SAT a realizar el diagnóstico y/o reparación del equipo descrito. En caso de requerir presupuesto, el cliente será informado previamente a su ejecución. El rechazo del presupuesto conllevará el abono de los gastos de diagnóstico. Los equipos no recogidos en un plazo de 60 días desde la notificación podrán ser considerados abandonados. El SAT no se responsabiliza de los datos almacenados en el dispositivo; se recomienda realizar copia de seguridad previa. La garantía de la reparación efectuada es de 3 meses sobre la pieza sustituida y mano de obra, no cubriendo daños preexistentes ni ajenos a la intervención realizada.'
                },
                {
                    title: 'CONSENTIMIENTO DEL CLIENTE',
                    body:  'El cliente declara haber leído y aceptado las presentes condiciones del servicio, haber sido informado del tratamiento de sus datos personales conforme a la normativa vigente, y exime al SAT de responsabilidad sobre la pérdida de datos contenidos en el dispositivo entregado.'
                }
            ];
            const legalFS   = 5.6;
            const legalLH   = 3.6;
            const legalPad  = 3;
            const legalColW = col - legalPad * 2;
            pdf.setFontSize(legalFS);
            let legalH = legalPad * 2;
            legalSections.forEach((s, i) => {
                legalH += 4.5;
                legalH += pdf.splitTextToSize(s.body, legalColW).length * legalLH;
                if (i < legalSections.length - 1) legalH += 2.5;
            });

            const sigBoxH   = 36;
            const sigBoxW   = 80;
            const legalBoxY = pageH - 4 - legalH;
            const sigBoxY   = legalBoxY - sigBoxH - 4;

            // checkPage respeta la zona reservada del fondo
            const checkPage = (needed = 10) => {
                if (y + needed > sigBoxY - 5) {
                    pdf.addPage();
                    y = margin;
                }
            };

            const sectionTitle = (title) => {
                checkPage(12);
                pdf.setFillColor(240, 235, 252);
                pdf.rect(margin, y - 4, col, 8, 'F');
                pdf.setFillColor(92, 34, 194);
                pdf.rect(margin, y - 4, 1.2, 8, 'F');
                pdf.setTextColor(92, 34, 194);
                pdf.setFontSize(10);
                pdf.setFont('helvetica', 'bold');
                pdf.text(title, margin + 4, y + 0.5);
                y += 9;
            };

            const row = (label, value, x = margin, maxW = col) => {
                checkPage(8);
                pdf.setFontSize(9);
                pdf.setFont('helvetica', 'bold');
                pdf.setTextColor(100, 100, 100);
                pdf.text(label, x, y);
                pdf.setFont('helvetica', 'normal');
                pdf.setTextColor(30, 30, 30);
                const lines = pdf.splitTextToSize(String(value || '—'), maxW - 28);
                pdf.text(lines, x + 27, y);
                y += lines.length * 5 + 1;
            };

            const halfRow = (label1, val1, label2, val2) => {
                checkPage(8);
                const half = col / 2;
                pdf.setFontSize(9);
                pdf.setFont('helvetica', 'bold');
                pdf.setTextColor(100, 100, 100);
                pdf.text(label1, margin, y);
                pdf.setFont('helvetica', 'normal');
                pdf.setTextColor(30, 30, 30);
                pdf.text(String(val1 || '—'), margin + 27, y);
                pdf.setFont('helvetica', 'bold');
                pdf.setTextColor(100, 100, 100);
                pdf.text(label2, margin + half, y);
                pdf.setFont('helvetica', 'normal');
                pdf.setTextColor(30, 30, 30);
                pdf.text(String(val2 || '—'), margin + half + 27, y);
                y += 6;
            };

            // ── Logo ──
            let logoData = null;
            try {
                const logoImg = new Image();
                logoImg.crossOrigin = 'anonymous';
                await new Promise((res, rej) => { logoImg.onload = res; logoImg.onerror = rej; logoImg.src = av_data.logo_url; });
                const logoCanvas = document.createElement('canvas');
                logoCanvas.width  = logoImg.naturalWidth;
                logoCanvas.height = logoImg.naturalHeight;
                const logoCtx = logoCanvas.getContext('2d');
                logoCtx.drawImage(logoImg, 0, 0);
                const imgData = logoCtx.getImageData(0, 0, logoCanvas.width, logoCanvas.height);
                const d = imgData.data;
                for (let i = 0; i < d.length; i += 4) {
                    if (d[i] < 60 && d[i+1] < 60 && d[i+2] < 60 && d[i+3] > 0) {
                        d[i] = 255; d[i+1] = 255; d[i+2] = 255;
                    }
                }
                logoCtx.putImageData(imgData, 0, 0);
                logoData = logoCanvas.toDataURL('image/png');
            } catch(e) { console.warn('Logo no cargado:', e); }

            // ── Cabecera empresa (fondo blanco) ──
            const logoSize = 28;
            const letterH  = logoSize + 10;   // altura del bloque cabecera
            const logoY    = 5;

            if (logoData) {
                pdf.addImage(logoData, 'PNG', margin, logoY, logoSize, logoSize);
            }

            const infoX = logoData ? margin + logoSize + 6 : margin;
            pdf.setTextColor(30, 30, 30);
            pdf.setFontSize(13);
            pdf.setFont('helvetica', 'bold');
            pdf.text('iCoreByte SAT · APP Informática', infoX, logoY + 8);
            pdf.setFontSize(8);
            pdf.setFont('helvetica', 'normal');
            pdf.setTextColor(100, 100, 100);
            pdf.text('Alex Valishin Abubekirov · 49298273G', infoX, logoY + 14);
            pdf.text('Carretera de Palamós 57, local · 17220 Sant Feliu de Guixols, Girona', infoX, logoY + 19);
            pdf.setTextColor(92, 34, 194);
            pdf.textWithLink('icorebyte.com', infoX, logoY + 24, { url: 'https://icorebyte.com/' });

            // ── Barra "Parte de Reparación" ──
            const barY  = letterH + 6;
            const barH2 = 19;
            pdf.setFillColor(240, 235, 252);
            pdf.rect(0, barY, pageW, barH2, 'F');
            pdf.setFillColor(92, 34, 194);
            pdf.rect(0, barY, 1.2, barH2, 'F');
            pdf.setTextColor(92, 34, 194);
            pdf.setFontSize(11);
            pdf.setFont('helvetica', 'bold');
            pdf.text('Parte de Reparación', margin + 2, barY + 7);
            pdf.setFontSize(12);
            pdf.text(`SAT #${satId}`, pageW - margin, barY + 7, { align: 'right' });
            pdf.setFontSize(8);
            pdf.setFont('helvetica', 'normal');
            pdf.setTextColor(130, 100, 180);
            pdf.text(new Date().toLocaleDateString('es-ES'), margin + 2, barY + 14);

            y = barY + barH2 + 8;

            // ── Cliente ──
            sectionTitle('CLIENTE');
            halfRow('Nombre:', client, 'DNI/NIE:', dni);
            halfRow('Teléfono:', phone, 'Atendido por:', attended);
            y += 3;

            // ── Equipo ──
            sectionTitle('EQUIPO');
            halfRow('Tipo:', equipLabel, 'Marca/Modelo:', model);
            row('Nº Serie / IMEI:', serial);
            if (accesories) row('Accesorios:', accesories);
            if (otherEquip)  row('Otro equipo:', otherEquip);
            if (otherAcces)  row('Otro accesorio:', otherAcces);
            y += 3;

            // ── Estado físico ──
            if (physCond) {
                sectionTitle('ESTADO FÍSICO');
                checkPage(8);
                pdf.setFontSize(9);
                pdf.setFont('helvetica', 'normal');
                pdf.setTextColor(30, 30, 30);
                const lines = pdf.splitTextToSize(physCond, col);
                lines.forEach(line => { checkPage(6); pdf.text(line, margin, y); y += 5; });
                y += 3;
            }

            // ── Incidencia ──
            sectionTitle('INCIDENCIA Y REPARACIÓN');
            if (incident)     row('Incidencia:', incident);
            if (repair)       row('Reparación:', repair);
            if (orderedParts) row('Piezas pedidas:', orderedParts);
            y += 3;


            // ── Firma ──
            if (sigImg) {
                sectionTitle('FIRMA DEL CLIENTE');
                try {
                    const canvas  = document.createElement('canvas');
                    const ctx     = canvas.getContext('2d');
                    const img     = new Image();
                    img.crossOrigin = 'anonymous';
                    await new Promise((res, rej) => { img.onload = res; img.onerror = rej; img.src = sigImg.src; });
                    canvas.width  = img.naturalWidth;
                    canvas.height = img.naturalHeight;
                    ctx.drawImage(img, 0, 0);
                    const imgW = 60;
                    const imgH = (img.naturalHeight / img.naturalWidth) * imgW;
                    checkPage(imgH + 5);
                    pdf.addImage(canvas.toDataURL('image/png'), 'PNG', margin, y, imgW, imgH);
                    y += imgH + 5;
                } catch (e) {
                    console.warn('Firma no cargada:', e);
                }
            }

            // ── Caja firma manual (posición fija) ──
            const sigBoxX = pageW - margin - sigBoxW;

            pdf.setDrawColor(200, 200, 200);
            pdf.setLineWidth(0.3);
            pdf.roundedRect(sigBoxX, sigBoxY, sigBoxW, sigBoxH, 2, 2);
            pdf.setFontSize(7.5);
            pdf.setFont('helvetica', 'bold');
            pdf.setTextColor(130, 130, 130);
            pdf.text('Firma del cliente', sigBoxX + sigBoxW / 2, sigBoxY + 6, { align: 'center' });
            pdf.setDrawColor(160, 160, 160);
            pdf.setLineWidth(0.4);
            pdf.line(sigBoxX + 5, sigBoxY + sigBoxH - 8, sigBoxX + sigBoxW - 5, sigBoxY + sigBoxH - 8);

            // ── Textos legales (posición fija, ancho completo) ──
            pdf.setFillColor(249, 249, 249);
            pdf.setDrawColor(220, 220, 220);
            pdf.setLineWidth(0.3);
            pdf.roundedRect(margin, legalBoxY, col, legalH, 2, 2, 'FD');

            let ly = legalBoxY + legalPad + 2;
            legalSections.forEach((s, i) => {
                pdf.setFontSize(6);
                pdf.setFont('helvetica', 'bold');
                pdf.setTextColor(90, 90, 90);
                pdf.text(s.title, margin + legalPad, ly);
                ly += 4.5;
                pdf.setFontSize(legalFS);
                pdf.setFont('helvetica', 'normal');
                pdf.setTextColor(130, 130, 130);
                pdf.splitTextToSize(s.body, legalColW).forEach(line => {
                    pdf.text(line, margin + legalPad, ly);
                    ly += legalLH;
                });
                if (i < legalSections.length - 1) ly += 2.5;
            });

            return { pdf, satId };
    };

    const av_generate_sat_pdf = () => {
        const btn = document.querySelector('.js-generate-sat-pdf');
        if (!btn) return;
        btn.addEventListener('click', async () => {
            const { pdf, satId } = await buildSatPdf();
            pdf.save(`SAT_${satId || 'nuevo'}.pdf`);
        });
    };

    const av_sat_auto_save_pdf = async () => {

        const params = new URLSearchParams(window.location.search);
        console.log('[SAT PDF] URL params:', window.location.search);

        if ( params.get('pdf') !== '1' ) { console.log('[SAT PDF] Sin ?pdf=1, saliendo'); return; }

        const form = document.querySelector('.c-sat-form__form');
        if ( ! form ) { console.warn('[SAT PDF] No hay formulario SAT'); return; }

        const satId = document.querySelector('#sat-id')?.value;
        console.log('[SAT PDF] sat_id:', satId);
        if ( ! satId ) { console.warn('[SAT PDF] Sin sat_id'); return; }

        try {
            console.log('[SAT PDF] Generando PDF...');
            const { pdf } = await buildSatPdf();
            const b64 = pdf.output('datauristring');
            console.log('[SAT PDF] PDF generado, tamaño:', b64.length);

            const body = new FormData();
            body.append( 'action',     'sat_guardar_pdf' );
            body.append( 'nonce',      av_data.nonce_pdf );
            body.append( 'sat_id',     satId );
            body.append( 'pdf_base64', b64 );

            console.log('[SAT PDF] Enviando AJAX a:', av_data.av_ajax_url);
            const res  = await fetch( av_data.av_ajax_url, { method: 'POST', body } );
            const json = await res.json();
            console.log('[SAT PDF] Respuesta AJAX:', json);

            if ( json.success ) {
                console.log('[SAT PDF] ✅ Guardado en:', json.data.url);

                // Actualizar botón "Enviar SAT" con la URL del PDF
                const waBtn   = document.getElementById('js-wa-send-sat');
                if ( waBtn ) {
                    const phone  = waBtn.dataset.phone;
                    const satNum = waBtn.dataset.satNum;
                    const msg    = encodeURIComponent(`Hola, adjunto el parte de reparación SAT #${satNum}:\n${json.data.url}`);
                    waBtn.href   = `https://wa.me/${phone}?text=${msg}`;
                    waBtn.target = '_blank';
                    waBtn.classList.remove('is-hidden');
                }

                window.history.replaceState( {}, '', window.location.pathname );
            } else {
                console.warn('[SAT PDF] ❌ Error:', json.data);
            }
        } catch (err) {
            console.warn('[SAT PDF] ❌ Excepción:', err);
        }
    };

    const av_start_funcs = () => {

        av_reset_vars_css()

        av_call_fn('.js-gallery__wrapper-image', av_gallery_image)

        av_call_fn('.js-gallery__remove-image', av_gallery_remove_image)

        av_call_fn('.js-header__hamburguer', av_header_hamburguer)

        av_call_fn('.js-header__collapse', av_header_collapse)

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

        av_call_fn('.c-sat-form__form', av_sat_form_validate_reparado)

        av_call_fn('.c-sat-form__form', av_sat_form_validate_finalizado)

        av_call_fn('.js-sat-form__signature-pad', av_sat_form_signature_pad)

        av_call_fn('.js-photo-field', av_sat_photo_fields)

        // Páginas sin formulario que solo muestran fotos (seguimiento del cliente)
        av_call_fn('.js-photo-zoom', av_photo_modal)

        av_call_fn('.js-generate-sat-pdf', av_generate_sat_pdf)

        av_sat_auto_save_pdf()

        av_call_fn('.js-remove-search-list-sats', av_remove_search_sat)

        av_call_fn('.js-filters-toggle', av_filters_toggle)

        av_call_fn('.js-usuarios-modal', av_usuarios_modal)

        av_call_fn('.js-servicios-modal', av_servicios_modal)

        av_call_fn('.js-sats-filter-form', av_sats_filter_async)

        av_call_fn('.js-clients-filter-form', av_clients_filter_async)

        av_call_fn('.js-facturas-filter-form', av_facturas_filter_async)

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

