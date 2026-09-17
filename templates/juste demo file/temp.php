<section class="ya-ebc-page" id="ya-ebc-page">
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Newsreader:opsz,wght@6..72,400;6..72,500;6..72,600;6..72,700&family=Open+Sans:wght@400;500;600;700&display=swap');

    html:has(.ya-ebc-page),
    body:has(.ya-ebc-page){
      margin:0!important;
      padding:0!important;
      overflow-x:clip!important;
      overflow-y:visible!important;
    }

    body:has(.ya-ebc-page) #page,
    body:has(.ya-ebc-page) .site,
    body:has(.ya-ebc-page) .site-content,
    body:has(.ya-ebc-page) #content,
    body:has(.ya-ebc-page) #primary,
    body:has(.ya-ebc-page) #main,
    body:has(.ya-ebc-page) .site-main,
    body:has(.ya-ebc-page) article,
    body:has(.ya-ebc-page) .inside-article,
    body:has(.ya-ebc-page) .entry-content{
      margin-top:0!important;
      margin-bottom:0!important;
      padding-top:0!important;
      padding-bottom:0!important;
      border-top:0!important;
      overflow:visible!important;
    }

    body:has(.ya-ebc-page) h1.entry-title,
    body:has(.ya-ebc-page) .entry-header,
    body:has(.ya-ebc-page) .page-header,
    body:has(.ya-ebc-page) .elementor-page-title,
    body:has(.ya-ebc-page) .page-title{
      display:none!important;
      height:0!important;
      margin:0!important;
      padding:0!important;
      overflow:hidden!important;
    }

    body:has(.ya-ebc-page) .elementor-location-header,
    body:has(.ya-ebc-page) #masthead,
    body:has(.ya-ebc-page) header.site-header{
      position:absolute!important;
      top:0!important;
      right:0!important;
      left:0!important;
      width:100%!important;
      margin:0!important;
      border:0!important;
      z-index:9999!important;
      background:transparent!important;
    }

    body:has(.ya-ebc-page) .elementor-section:has(.ya-ebc-page),
    body:has(.ya-ebc-page) .elementor-container:has(.ya-ebc-page),
    body:has(.ya-ebc-page) .elementor-column:has(.ya-ebc-page),
    body:has(.ya-ebc-page) .elementor-widget-wrap:has(.ya-ebc-page),
    body:has(.ya-ebc-page) .elementor-widget-html:has(.ya-ebc-page),
    body:has(.ya-ebc-page) .elementor-widget-html:has(.ya-ebc-page)>.elementor-widget-container,
    body:has(.ya-ebc-page) .e-con:has(.ya-ebc-page),
    body:has(.ya-ebc-page) .e-con-inner:has(.ya-ebc-page),
    body:has(.ya-ebc-page) .elementor-element:has(.ya-ebc-page){
      width:100%!important;
      max-width:none!important;
      margin:0!important;
      padding-top:0!important;
      padding-bottom:0!important;
      overflow:visible!important;
    }

    .ya-ebc-page,
    .ya-ebc-page *{box-sizing:border-box}

    .ya-ebc-page{
      --green:#022f2e;
      --green-2:#045857;
      --green-3:#0d6d69;
      --gold:#c6a45f;
      --ivory:#f7f3ea;
      --cream:#fcfaf5;
      --ink:#173331;
      --muted:#667775;
      --line:#dfe7e4;
      --shadow:0 24px 70px rgba(14,49,47,.14);
      width:100vw;
      max-width:100vw;
      margin:-1px 0 0 calc(50% - 50vw)!important;
      padding:0!important;
      overflow:visible;
      background:#fff;
      color:var(--ink);
      font-family:Inter,Arial,sans-serif;
      line-height:1.6;
      position:relative;
      z-index:1;
    }

    .ya-ebc-page button,
    .ya-ebc-page input,
    .ya-ebc-page select,
    .ya-ebc-page textarea{font:inherit}

    .ya-ebc-page a{color:inherit}
    .ya-ebc-page img{display:block;max-width:100%}

    .ya-ebc-page .ya-inner{
      width:min(1220px,calc(100% - 48px));
      margin:0 auto;
      position:relative;
    }

    .ya-ebc-page .ya-script{
      font-family:"Cormorant Garamond",serif;
      font-weight:600;
      letter-spacing:-.02em;
    }

    .ya-ebc-page .ya-serif{font-family:"Cormorant Garamond",serif}

    .ya-ebc-page .ya-kicker{
      display:inline-flex;
      align-items:center;
      gap:10px;
      color:var(--gold);
      font-size:11px;
      font-weight:800;
      letter-spacing:2.6px;
      text-transform:uppercase;
    }

    .ya-ebc-page .ya-kicker:before{
      content:"";
      width:34px;
      height:1px;
      background:currentColor;
    }

    .ya-ebc-page .ya-btn{
      display:inline-flex;
      align-items:center;
      justify-content:center;
      gap:10px;
      min-height:52px;
      padding:0 27px;
      border:1px solid transparent;
      border-radius:999px;
      text-decoration:none;
      font-size:11px;
      font-weight:800;
      letter-spacing:1.5px;
      text-transform:uppercase;
      cursor:pointer;
      transition:transform .3s ease,background .3s ease,color .3s ease,border-color .3s ease;
    }

    .ya-ebc-page .ya-btn svg{width:17px;height:17px;flex:0 0 auto}
    .ya-ebc-page .ya-btn:hover{transform:translateY(-3px)}
    .ya-ebc-page .ya-btn-primary{background:var(--green-2);color:#fff}
    .ya-ebc-page .ya-btn-primary:hover{background:var(--green);color:#fff}
    .ya-ebc-page .ya-btn-light{background:#fff;color:var(--green)}
    .ya-ebc-page .ya-btn-light:hover{background:var(--gold);color:var(--green)}
    .ya-ebc-page .ya-btn-outline{border-color:rgba(255,255,255,.72);background:transparent;color:#fff}
    .ya-ebc-page .ya-btn-outline:hover{background:#fff;color:var(--green)}

    .ya-ebc-page .ya-reveal{
      opacity:0;
      transform:translateY(28px);
      transition:opacity .75s ease,transform .75s ease;
    }

    .ya-ebc-page .ya-reveal.is-visible{opacity:1;transform:none}

    /* HERO */
    .ya-ebc-page .ya-hero{
      min-height:100vh;
      min-height:100svh;
      display:flex;
      align-items:flex-end;
      position:relative;
      isolation:isolate;
      padding:160px 0 96px;
      background-image:url("<?php echo esc_url( $hero_bg_img ); ?>");
      background-position:center;
      background-size:cover;
      background-repeat:no-repeat;
    }

    .ya-ebc-page .ya-hero:before{
      content:"";
      position:absolute;
      inset:0;
      z-index:-1;
      background:rgba(2,35,34,.46);
    }

    .ya-ebc-page .ya-hero-content{
      width:min(920px,100%);
      color:#fff;
    }

    .ya-ebc-page .ya-breadcrumbs{
      display:flex;
      flex-wrap:wrap;
      align-items:center;
      gap:8px;
      margin-bottom:25px;
      font-size:11px;
      font-weight:700;
      letter-spacing:1.1px;
      text-transform:uppercase;
      color:rgba(255,255,255,.78);
    }

    .ya-ebc-page .ya-breadcrumbs a{text-decoration:none}
    .ya-ebc-page .ya-breadcrumbs a:hover{color:#fff}
    .ya-ebc-page .ya-breadcrumbs svg{width:13px;height:13px}

    .ya-ebc-page .ya-hero .ya-script{
      margin-bottom:10px;
      font-size:clamp(42px,5vw,72px);
      line-height:.9;
      color:#fff;
      text-shadow:0 14px 40px rgba(0,0,0,.25);
    }

    .ya-ebc-page .ya-hero h1{
      max-width:930px;
      margin:0;
      font-family:"Cormorant Garamond",serif;
      font-size:clamp(44px,6.5vw,92px);
      line-height:.9;
      font-weight:600;
      color:#fff;
    }

    .ya-ebc-page .ya-hero-subtitle{
      max-width:760px;
      margin:25px 0 30px;
      font-size:16px;
      line-height:1.8;
      color:rgba(255,255,255,.9);
    }

    .ya-ebc-page .ya-hero-actions{display:flex;flex-wrap:wrap;gap:12px}

    .ya-ebc-page .ya-hero-badge{
      position:absolute;
      right:0;
      bottom:0;
      width:190px;
      height:190px;
      display:grid;
      place-items:center;
      border-radius:50% 50% 0 50%;
      background:var(--green);
      color:#fff;
      text-align:center;
      box-shadow:0 24px 60px rgba(0,0,0,.2);
    }

    .ya-ebc-page .ya-hero-badge strong{
      display:block;
      font-family:"Cormorant Garamond",serif;
      font-size:54px;
      line-height:.8;
      color:var(--gold);
    }

    .ya-ebc-page .ya-hero-badge span{
      display:block;
      margin-top:8px;
      font-size:10px;
      font-weight:800;
      letter-spacing:1.6px;
      text-transform:uppercase;
    }

    /* 2. QUICK FACTS FLOATING CARD */
    .ya-ebc-page .ya-facts{
      position:relative;
      z-index:10;
      margin:-36px auto 28px;
      width:min(100% - 40px, 1400px);
      padding:0;
    }
    .ya-ebc-page .ya-facts-grid{
      display:grid;
      grid-template-columns:repeat(5,1fr);
      background:#ffffff;
      border:1px solid #edf0ea;
      border-radius:18px;
      box-shadow:0 4px 20px rgba(0,0,0,0.04), 0 1px 3px rgba(0,0,0,0.02);
      padding:16px 22px;
      gap:16px;
      align-items:center;
      width:100%;
      box-sizing:border-box;
    }
    .ya-ebc-page .ya-fact{
      display:flex;
      align-items:center;
      gap:14px;
      min-width:0;
      min-height:auto;
      padding:4px 6px;
      border:0;
    }
    .ya-ebc-page .ya-fact:last-child{border:0}
    .ya-ebc-page .ya-fact-icon{
      width:44px;
      height:44px;
      flex:0 0 44px;
      border-radius:12px;
      background:#fffaf0;
      border:1px solid #fed7aa;
      color:#c05621;
      display:flex;
      align-items:center;
      justify-content:center;
    }
    .ya-ebc-page .ya-fact-icon svg{
      width:20px;
      height:20px;
      stroke:#c05621;
      stroke-width:1.8;
      fill:none;
    }
    .ya-ebc-page .ya-fact-text,
    .ya-ebc-page .ya-fact div{
      min-width:0;
    }
    .ya-ebc-page .ya-fact small{
      display:block;
      margin-bottom:2px;
      color:#9ca3af;
      font-size:10px;
      font-weight:700;
      letter-spacing:0.08em;
      text-transform:uppercase;
      line-height:1.2;
    }
    .ya-ebc-page .ya-fact strong{
      display:block;
      font-size:14px;
      font-weight:700;
      color:#111827;
      line-height:1.35;
      white-space:nowrap;
      overflow:hidden;
      text-overflow:ellipsis;
    }

    /* TABS */

    .ya-tab-mman-section{
      width:100%;
      display:grid;
      justify-content:center;
      align-items:center;
      margin:0 auto;
    }
    .ya-ebc-page .ya-tabs-wrap{
      position:sticky;
      top:78px;
      z-index:80;
      margin-top:46px;
      background:#fff;
      border-top:1px solid var(--line);
      border-bottom:1px solid var(--line);
      box-shadow:0 10px 30px rgba(15,48,46,.06);
    }

    .ya-ebc-page .ya-tabs{
      display:flex;
      align-items:stretch;
      overflow-x:auto;
      scrollbar-width:none;
    }

    .ya-ebc-page .ya-tabs::-webkit-scrollbar{display:none}

    .ya-ebc-page .ya-tab{
      position:relative;
      min-width:max-content;
      min-height:70px;
      display:inline-flex;
      align-items:center;
      justify-content:center;
      gap:9px;
      padding:0 25px;
      border:0;
      background:#fff;
      color:#586a68;
      font-size:10px;
      font-weight:800;
      letter-spacing:1.35px;
      text-transform:uppercase;
      cursor:pointer;
      transition:background .25s ease,color .25s ease;
    }

    .ya-ebc-page .ya-tab svg{width:17px;height:17px}
    .ya-ebc-page .ya-tab:hover{background:var(--cream);color:var(--green)}
    .ya-ebc-page .ya-tab.is-active{background:var(--green);color:#fff}

    /* TAB CONTENT */
    .ya-ebc-page .ya-tab-stage{background:#fff}
    .ya-ebc-page .ya-panel{display:none;padding:105px 0 118px}
    .ya-ebc-page .ya-panel.is-active{display:block;animation:yaPanelIn .5s ease both}
    @keyframes yaPanelIn{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:none}}

    .ya-ebc-page .ya-section-heading{
      max-width:850px;
      margin-bottom:46px;
    }

    .ya-ebc-page .ya-section-heading .ya-script{
      margin:8px 0 12px;
      font-size:clamp(32px,4vw,48px);
      line-height:1;
      color:#3b5150;
    }

    .ya-ebc-page .ya-section-heading h2{
      margin:0;
      font-family:"Cormorant Garamond",serif;
      font-size:clamp(44px,5vw,76px);
      line-height:.92;
      font-weight:600;
      color:var(--green);
    }

    .ya-ebc-page .ya-section-heading p{
      max-width:720px;
      margin:19px 0 0;
      color:var(--muted);
      font-size:15.5px;
      line-height:1.85;
    }

    /* OVERVIEW */
    .ya-ebc-page .ya-overview-grid{
      display:grid;
      grid-template-columns:1.08fr .92fr;
      gap:74px;
      align-items:start;
    }

    .ya-ebc-page .ya-overview-copy h3{
      margin:0 0 24px;
      font-family:"Cormorant Garamond",serif;
      font-size:clamp(38px,4vw,60px);
      line-height:.98;
      font-weight:600;
      color:var(--green);
    }

    .ya-ebc-page .ya-overview-copy p{
      margin:0 0 20px;
      color:#546865;
      font-size:16px;
      line-height:1.9;
    }

    .ya-ebc-page .ya-highlight-box{
      margin:30px 0;
      padding:27px 28px;
      border-left:3px solid var(--gold);
      background:var(--ivory);
      font-family:"Cormorant Garamond",serif;
      font-size:24px;
      line-height:1.34;
      color:var(--green);
    }

    .ya-ebc-page .ya-overview-actions{display:flex;flex-wrap:wrap;gap:12px;margin-top:30px}
    .ya-ebc-page .ya-btn-secondary{border-color:var(--green-2);background:#fff;color:var(--green-2)}
    .ya-ebc-page .ya-btn-secondary:hover{background:var(--green-2);color:#fff}

    .ya-ebc-page .ya-map-card{
      position:sticky;
      top:180px;
      overflow:hidden;
      border-radius:30px;
      background:var(--cream);
      box-shadow:var(--shadow);
    }

    .ya-ebc-page .ya-map-image{
      position:relative;
      height:560px;
      overflow:hidden;
      background:#eef3ef;
    }

    .ya-ebc-page .ya-map-image img{
      width:100%;
      height:100%;
      object-fit:cover;
      object-position:center;
    }

    .ya-ebc-page .ya-map-image button{
      position:absolute;
      right:18px;
      bottom:18px;
      width:52px;
      height:52px;
      display:grid;
      place-items:center;
      border:0;
      border-radius:50%;
      background:#fff;
      color:var(--green);
      box-shadow:0 12px 34px rgba(0,0,0,.18);
      cursor:pointer;
    }

    .ya-ebc-page .ya-map-image button svg{width:21px;height:21px}
    .ya-ebc-page .ya-map-caption{padding:25px 27px}
    .ya-ebc-page .ya-map-caption small{
      display:block;
      margin-bottom:5px;
      color:var(--gold);
      font-size:9px;
      font-weight:800;
      letter-spacing:1.6px;
      text-transform:uppercase;
    }
    .ya-ebc-page .ya-map-caption h3{
      margin:0;
      font-family:"Cormorant Garamond",serif;
      font-size:31px;
      color:var(--green);
    }

    .ya-ebc-page .ya-inclusions{
      display:grid;
      grid-template-columns:repeat(2,1fr);
      gap:18px;
      margin-top:38px;
    }

    .ya-ebc-page .ya-inclusion{
      display:flex;
      align-items:flex-start;
      gap:14px;
      padding:20px 22px;
      border:1px solid var(--line);
      border-radius:18px;
      background:#fff;
      box-shadow:0 8px 24px rgba(18,52,50,.03);
    }

    .ya-ebc-page .ya-inclusion svg{width:20px;height:20px;flex:0 0 20px;margin-top:2px;color:var(--green-2)}
    .ya-ebc-page .ya-inclusion strong{display:block;font-size:15px;font-weight:700;color:var(--green);margin-bottom:4px}
    .ya-ebc-page .ya-inclusion .ya-inclusion-desc{font-size:13px;line-height:1.6;color:#526462}
    .ya-ebc-page .ya-inclusion .ya-inclusion-desc p{margin:0 0 6px}
    .ya-ebc-page .ya-inclusion .ya-inclusion-desc p:last-child{margin-bottom:0}
    .ya-ebc-page .ya-inclusion span{font-size:13px;line-height:1.55;color:#526462}

    /* ITINERARY */
    .ya-ebc-page .ya-itinerary-shell{
      display:block;
      width:100%;
    }

    .ya-ebc-page .ya-days{display:grid;gap:13px}
    .ya-ebc-page .ya-day{
      overflow:hidden;
      border:1px solid var(--line);
      border-radius:20px;
      background:#fff;
      box-shadow:0 12px 34px rgba(18,52,50,.05);
    }

    .ya-ebc-page .ya-day-head{
      width:100%;
      display:grid;
      grid-template-columns:72px minmax(0,1fr) auto;
      align-items:center;
      gap:20px;
      padding:21px 24px;
      border:0;
      background:#fff;
      color:var(--green);
      text-align:left;
      cursor:pointer;
    }

    .ya-ebc-page .ya-day-number{
      width:58px;
      height:58px;
      display:grid;
      place-items:center;
      border-radius:18px;
      background:var(--ivory);
      font-family:"Cormorant Garamond",serif;
      font-size:26px;
      font-weight:700;
      color:var(--green-2);
    }

    .ya-ebc-page .ya-day-title small{
      display:block;
      margin-bottom:4px;
      color:var(--gold);
      font-size:9px;
      font-weight:800;
      letter-spacing:1.4px;
      text-transform:uppercase;
    }

    .ya-ebc-page .ya-day-title strong{
      display:block;
      font-family:"Cormorant Garamond",serif;
      font-size:25px;
      line-height:1.1;
      font-weight:600;
      color:var(--green);
    }

    .ya-ebc-page .ya-day-toggle{
      width:42px;
      height:42px;
      display:grid;
      place-items:center;
      border-radius:50%;
      background:var(--green);
      color:#fff;
      transition:transform .3s ease,background .3s ease;
    }

    .ya-ebc-page .ya-day-toggle svg{width:18px;height:18px;transition:transform .3s ease}
    .ya-ebc-page .ya-day.is-open .ya-day-toggle{background:var(--gold);color:var(--green)}
    .ya-ebc-page .ya-day.is-open .ya-day-toggle svg{transform:rotate(45deg)}

    .ya-ebc-page .ya-day-body{
      display:grid;
      grid-template-rows:0fr;
      transition:grid-template-rows .4s ease;
    }

    .ya-ebc-page .ya-day.is-open .ya-day-body{grid-template-rows:1fr}
    .ya-ebc-page .ya-day-body-inner{overflow:hidden}
    .ya-ebc-page .ya-day-content{padding:0 25px 26px 116px}
    .ya-ebc-page .ya-day-content p{margin:0 0 18px;color:#5b6c69;font-size:14px;line-height:1.85}

    .ya-ebc-page .ya-day-meta{
      display:flex;
      flex-wrap:wrap;
      gap:9px;
    }

    .ya-ebc-page .ya-meta-pill{
      display:inline-flex;
      align-items:center;
      gap:7px;
      min-height:34px;
      padding:0 12px;
      border-radius:999px;
      background:var(--ivory);
      color:#4d605e;
      font-size:10px;
      font-weight:700;
    }

    .ya-ebc-page .ya-meta-pill svg{width:14px;height:14px;color:var(--green-2)}

    /* GALLERY */
    .ya-ebc-page .ya-gallery-grid{
      display:grid;
      grid-template-columns:1.2fr .8fr .8fr;
      grid-template-rows:330px 330px;
      gap:16px;
    }

    .ya-ebc-page .ya-gallery-item{
      position:relative;
      overflow:hidden;
      border:0;
      border-radius:24px;
      padding:0;
      background:#e8efed;
      cursor:pointer;
    }

    .ya-ebc-page .ya-gallery-item:first-child{grid-row:1/3}
    .ya-ebc-page .ya-gallery-item:nth-child(4){grid-column:2/4}
    .ya-ebc-page .ya-gallery-item img{width:100%;height:100%;object-fit:cover;transition:transform .6s ease}
    .ya-ebc-page .ya-gallery-item:hover img{transform:scale(1.055)}
    .ya-ebc-page .ya-gallery-label{
      position:absolute;
      left:18px;
      right:18px;
      bottom:18px;
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:14px;
      padding:14px 16px;
      border-radius:15px;
      background:rgba(2,47,46,.88);
      color:#fff;
      text-align:left;
    }

    .ya-ebc-page .ya-gallery-label strong{font-family:"Cormorant Garamond",serif;font-size:21px;font-weight:600}
    .ya-ebc-page .ya-gallery-label svg{width:18px;height:18px;flex:0 0 auto}

    /* ESSENTIAL INFO */
    .ya-ebc-page .ya-essential-layout{
      display:grid;
      grid-template-columns:1fr 380px;
      gap:42px;
      align-items:start;
    }

    .ya-ebc-page .ya-info-list{display:grid;gap:13px}
    .ya-ebc-page .ya-info-item{border:1px solid var(--line);border-radius:19px;overflow:hidden;background:#fff}
    .ya-ebc-page .ya-info-head{
      width:100%;
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:20px;
      padding:21px 23px;
      border:0;
      background:#fff;
      color:var(--green);
      text-align:left;
      cursor:pointer;
    }
    .ya-ebc-page .ya-info-head span{display:flex;align-items:center;gap:13px;font-family:"Cormorant Garamond",serif;font-size:26px;font-weight:600}
    .ya-ebc-page .ya-info-head span svg{width:21px;height:21px;color:var(--green-2)}
    .ya-ebc-page .ya-info-head>svg{width:18px;height:18px;transition:transform .3s ease}
    .ya-ebc-page .ya-info-item.is-open .ya-info-head>svg{transform:rotate(45deg)}
    .ya-ebc-page .ya-info-body{display:grid;grid-template-rows:0fr;transition:grid-template-rows .35s ease}
    .ya-ebc-page .ya-info-item.is-open .ya-info-body{grid-template-rows:1fr}
    .ya-ebc-page .ya-info-body>div{overflow:hidden}
    .ya-ebc-page .ya-info-content{padding:0 24px 24px 57px;color:#5b6d6a;font-size:14px;line-height:1.85}
    .ya-ebc-page .ya-info-content p{margin:0 0 13px}
    .ya-ebc-page .ya-info-content ul{margin:0;padding-left:19px}
    .ya-ebc-page .ya-info-content li{margin:6px 0}

    .ya-ebc-page .ya-trip-note-card{
      position:sticky;
      top:180px;
      overflow:hidden;
      border-radius:28px;
      background:var(--green);
      color:#fff;
      box-shadow:var(--shadow);
    }

    .ya-ebc-page .ya-trip-note-image{
      height:230px;
      background:url("<?php echo esc_url( $card_img ); ?>") center/cover no-repeat;
    }

    .ya-ebc-page .ya-trip-note-copy{padding:29px}
    .ya-ebc-page .ya-trip-note-copy small{color:var(--gold);font-size:9px;font-weight:800;letter-spacing:1.5px;text-transform:uppercase}
    .ya-ebc-page .ya-trip-note-copy h3{margin:10px 0 14px;font-family:"Cormorant Garamond",serif;font-size:36px;line-height:1;color:#fff}
    .ya-ebc-page .ya-trip-note-copy p{margin:0 0 22px;color:rgba(255,255,255,.72);font-size:13.5px;line-height:1.75}

    /* REVIEWS */
    .ya-ebc-page .ya-review-layout{
      display:grid;
      grid-template-columns:.95fr 1.05fr;
      gap:54px;
      align-items:start;
    }

    .ya-ebc-page .ya-review-cards{display:grid;gap:17px}
    .ya-ebc-page .ya-review-card{
      padding:28px;
      border:1px solid var(--line);
      border-radius:24px;
      background:#fff;
      box-shadow:0 14px 38px rgba(16,50,48,.06);
    }

    .ya-ebc-page .ya-stars{display:flex;gap:3px;margin-bottom:16px;color:var(--gold)}
    .ya-ebc-page .ya-stars svg{width:17px;height:17px;fill:currentColor}
    .ya-ebc-page .ya-review-card blockquote{margin:0 0 20px;font-family:"Cormorant Garamond",serif;font-size:25px;line-height:1.35;color:var(--green)}
    .ya-ebc-page .ya-review-card strong{display:block;font-size:12px;color:var(--green)}
    .ya-ebc-page .ya-review-card small{color:#80908e;font-size:10px}

    .ya-ebc-page .ya-review-form{
      padding:34px;
      border-radius:28px;
      background:var(--cream);
      box-shadow:var(--shadow);
    }

    .ya-ebc-page .ya-review-form h3{margin:0 0 8px;font-family:"Cormorant Garamond",serif;font-size:39px;line-height:1;color:var(--green)}
    .ya-ebc-page .ya-review-form>p{margin:0 0 25px;color:var(--muted);font-size:13.5px}
    .ya-ebc-page .ya-form-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:15px}
    .ya-ebc-page .ya-field{display:grid;gap:7px}
    .ya-ebc-page .ya-field-full{grid-column:1/-1}
    .ya-ebc-page .ya-field label{font-size:9px;font-weight:800;letter-spacing:1.15px;text-transform:uppercase;color:#415855}
    .ya-ebc-page .ya-field input,
    .ya-ebc-page .ya-field select,
    .ya-ebc-page .ya-field textarea{
      width:100%;
      border:1px solid #d7e0de;
      border-radius:13px;
      background:#fff;
      color:var(--ink);
      outline:none;
      box-sizing:border-box;
    }
    .ya-ebc-page .ya-field input,
    .ya-ebc-page .ya-field select{height:50px;padding:0 14px}
    .ya-ebc-page .ya-field textarea{min-height:120px;padding:13px 14px;resize:vertical}
    .ya-ebc-page .ya-field input:focus,
    .ya-ebc-page .ya-field select:focus,
    .ya-ebc-page .ya-field textarea:focus{border-color:var(--green-2);box-shadow:0 0 0 4px rgba(4,88,87,.1)}

    .ya-ebc-page .ya-rating-row{display:flex;flex-direction:row-reverse;justify-content:flex-end;gap:4px}
    .ya-ebc-page .ya-rating-row input{position:absolute;opacity:0;pointer-events:none}
    .ya-ebc-page .ya-rating-row label{cursor:pointer;color:#cbd5d2}
    .ya-ebc-page .ya-rating-row label svg{width:28px;height:28px;fill:currentColor}
    .ya-ebc-page .ya-rating-row label:hover,
    .ya-ebc-page .ya-rating-row label:hover~label,
    .ya-ebc-page .ya-rating-row input:checked~label{color:var(--gold)}

    .ya-review-alert{padding:14px 18px;border-radius:12px;margin-bottom:16px;font-size:13px;display:none}
    .ya-review-alert--success{background:#e6f6ec;color:#0a7a3b;border:1px solid #a3e6be}
    .ya-review-alert--error{background:#fde8e8;color:#9b1c1c;border:1px solid #f8b4b4}

    /* RELATED */
    .ya-ebc-page .ya-related{
      padding:115px 0 125px;
      background:var(--cream);
    }

    .ya-ebc-page .ya-related-head{
      display:flex;
      align-items:end;
      justify-content:space-between;
      gap:30px;
      margin-bottom:42px;
    }

    .ya-ebc-page .ya-related-head .ya-script{font-size:42px;line-height:1;color:#3d5250}
    .ya-ebc-page .ya-related-head h2{margin:13px 0 0;font-family:"Cormorant Garamond",serif;font-size:59px;line-height:.92;color:var(--green)}
    .ya-ebc-page .ya-related-head p{max-width:460px;margin:0;color:var(--muted);font-size:14px;line-height:1.75}

    .ya-ebc-page .ya-related-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:17px}
    .ya-ebc-page .ya-related-card{overflow:hidden;border-radius:24px;background:#fff;text-decoration:none;box-shadow:0 15px 42px rgba(16,50,48,.08);transition:transform .35s ease}
    .ya-ebc-page .ya-related-card:hover{transform:translateY(-8px)}
    .ya-ebc-page .ya-related-image{height:245px;overflow:hidden}
    .ya-ebc-page .ya-related-image img{width:100%;height:100%;object-fit:cover;transition:transform .65s ease}
    .ya-ebc-page .ya-related-card:hover img{transform:scale(1.06)}
    .ya-ebc-page .ya-related-copy{padding:23px}
    .ya-ebc-page .ya-related-copy small{display:block;margin-bottom:9px;color:var(--gold);font-size:9px;font-weight:800;letter-spacing:1.4px;text-transform:uppercase}
    .ya-ebc-page .ya-related-copy h3{margin:0 0 18px;font-family:"Cormorant Garamond",serif;font-size:29px;line-height:1.05;font-weight:600;color:var(--green)}
    .ya-ebc-page .ya-related-link{display:flex;align-items:center;justify-content:space-between;padding-top:16px;border-top:1px solid var(--line);font-size:9px;font-weight:800;letter-spacing:1.2px;text-transform:uppercase;color:var(--green-2)}
    .ya-ebc-page .ya-related-link svg{width:17px;height:17px}

    /* LIGHTBOX */
    .ya-ebc-page .ya-lightbox{
      position:fixed;
      inset:0;
      z-index:100000;
      display:none;
      align-items:center;
      justify-content:center;
      padding:34px;
      background:rgba(1,24,23,.94);
    }
    .ya-ebc-page .ya-lightbox.is-open{display:flex}
    .ya-ebc-page .ya-lightbox img{max-width:min(1100px,94vw);max-height:86vh;border-radius:18px;box-shadow:0 30px 100px rgba(0,0,0,.45)}
    .ya-ebc-page .ya-lightbox-close{
      position:absolute;
      top:22px;
      right:22px;
      width:48px;
      height:48px;
      display:grid;
      place-items:center;
      border:1px solid rgba(255,255,255,.3);
      border-radius:50%;
      background:rgba(255,255,255,.08);
      color:#fff;
      cursor:pointer;
    }
    .ya-ebc-page .ya-lightbox-close svg{width:21px;height:21px}

    @media(max-width:1100px){
      .ya-ebc-page .ya-facts-grid{grid-template-columns:repeat(3,1fr)}
      .ya-ebc-page .ya-fact:nth-child(3){border-right:0}
      .ya-ebc-page .ya-fact:nth-child(n+4){border-top:1px solid var(--line)}
      .ya-ebc-page .ya-overview-grid,
      .ya-ebc-page .ya-review-layout{grid-template-columns:1fr}
      .ya-ebc-page .ya-map-card,
      .ya-ebc-page .ya-trip-note-card{position:relative;top:auto}
      .ya-ebc-page .ya-map-image{height:600px}
      .ya-ebc-page .ya-itinerary-shell{grid-template-columns:260px 1fr;gap:28px}
      .ya-ebc-page .ya-essential-layout{grid-template-columns:1fr 330px}
      .ya-ebc-page .ya-related-grid{grid-template-columns:repeat(2,1fr)}
      .ya-ebc-page .ya-hero-badge{right:24px}
    }

    @media(max-width:820px){
      .ya-ebc-page .ya-inner{width:min(100% - 30px,1220px)}
      .ya-ebc-page .ya-hero{min-height:92vh;min-height:92svh;padding:135px 0 78px;background-position:62% center}
      .ya-ebc-page .ya-hero .ya-script{font-size:48px}
      .ya-ebc-page .ya-hero h1{font-size:48px}
      .ya-ebc-page .ya-hero-subtitle{font-size:14px}
      .ya-ebc-page .ya-hero-badge{display:none}
      .ya-ebc-page .ya-facts{margin-top:-30px}
      .ya-ebc-page .ya-facts-grid{grid-template-columns:repeat(2,1fr)}
      .ya-ebc-page .ya-fact{min-height:96px;padding:17px;border-top:1px solid var(--line);border-right:1px solid var(--line)!important}
      .ya-ebc-page .ya-fact:nth-child(odd){border-right:1px solid var(--line)!important}
      .ya-ebc-page .ya-fact:nth-child(even){border-right:0!important}
      .ya-ebc-page .ya-fact:first-child,
      .ya-ebc-page .ya-fact:nth-child(2){border-top:0}
      .ya-ebc-page .ya-fact:last-child{grid-column:1/-1;border-right:0!important}
      .ya-ebc-page .ya-tabs-wrap{top:68px;margin-top:32px}
      .ya-ebc-page .ya-tab{min-height:60px;padding:0 18px}
      .ya-ebc-page .ya-panel{padding:78px 0 88px}
      .ya-ebc-page .ya-overview-grid,
      .ya-ebc-page .ya-itinerary-shell,
      .ya-ebc-page .ya-essential-layout{grid-template-columns:1fr}
      .ya-ebc-page .ya-gallery-grid{grid-template-columns:1fr 1fr;grid-template-rows:360px 260px 260px}
      .ya-ebc-page .ya-gallery-item:first-child{grid-column:1/3;grid-row:auto}
      .ya-ebc-page .ya-gallery-item:nth-child(4){grid-column:auto}
      .ya-ebc-page .ya-related-head{display:block}
      .ya-ebc-page .ya-related-head p{margin-top:18px}
    }

    @media(max-width:560px){
      .ya-ebc-page .ya-hero-actions{display:grid}
      .ya-ebc-page .ya-btn{width:100%}
      .ya-ebc-page .ya-hero h1{font-size:40px}
      .ya-ebc-page .ya-hero .ya-script{font-size:42px}
      .ya-ebc-page .ya-breadcrumbs{font-size:9px}
      .ya-ebc-page .ya-fact{gap:10px;padding:13px}
      .ya-ebc-page .ya-fact-icon{width:38px;height:38px;flex-basis:38px}
      .ya-ebc-page .ya-fact strong{font-size:11px}
      .ya-ebc-page .ya-section-heading .ya-script{font-size:34px}
      .ya-ebc-page .ya-section-heading h2{font-size:42px}
      .ya-ebc-page .ya-inclusions{grid-template-columns:1fr}
      .ya-ebc-page .ya-map-image{height:470px}
      .ya-ebc-page .ya-day-head{grid-template-columns:54px minmax(0,1fr) 38px;gap:12px;padding:16px}
      .ya-ebc-page .ya-day-number{width:48px;height:48px;border-radius:14px;font-size:22px}
      .ya-ebc-page .ya-day-title strong{font-size:20px}
      .ya-ebc-page .ya-day-toggle{width:36px;height:36px}
      .ya-ebc-page .ya-day-content{padding:0 17px 20px 17px}
      .ya-ebc-page .ya-gallery-grid{display:grid;grid-template-columns:1fr;grid-template-rows:none}
      .ya-ebc-page .ya-gallery-item,
      .ya-ebc-page .ya-gallery-item:first-child{grid-column:auto;grid-row:auto;height:300px}
      .ya-ebc-page .ya-info-content{padding:0 19px 22px}
      .ya-ebc-page .ya-review-form{padding:25px 18px}
      .ya-ebc-page .ya-form-grid{grid-template-columns:1fr}
      .ya-ebc-page .ya-field-full{grid-column:auto}
      .ya-ebc-page .ya-related-grid{grid-template-columns:1fr}
      .ya-ebc-page .ya-related-head .ya-script{font-size:36px}
      .ya-ebc-page .ya-related-head h2{font-size:43px}
      .ya-ebc-page .ya-lightbox{padding:18px}
    }

    @media(prefers-reduced-motion:reduce){
      .ya-ebc-page *{scroll-behavior:auto!important;animation-duration:.01ms!important;animation-iteration-count:1!important;transition-duration:.01ms!important}
      .ya-ebc-page .ya-reveal{opacity:1;transform:none}
    }

    /* Editorial refinement: presentation only — data, markup and interactions remain unchanged. */
    .ya-ebc-page{
      --green:#202821;
      --green-2:#617654;
      --green-3:#75896a;
      --gold:#D1AD54;
      --ivory:#f6f3ed;
      --cream:#fbfaf7;
      --ink:#20231f;
      --muted:#677068;
      --line:#dcdcd3;
      --shadow:0 18px 50px rgba(27,35,28,.08);
      font-family:"Open Sans",Arial,sans-serif;
      background:var(--cream);
    }
    .ya-ebc-page .ya-script,
    .ya-ebc-page .ya-serif,
    .ya-ebc-page .ya-hero h1,
    .ya-ebc-page .ya-section-heading h2,
    .ya-ebc-page .ya-overview-copy h3,
    .ya-ebc-page .ya-day-number,
    .ya-ebc-page .ya-day-title strong,
    .ya-ebc-page .ya-info-head span,
    .ya-ebc-page .ya-trip-note-copy h3,
    .ya-ebc-page .ya-review-card blockquote,
    .ya-ebc-page .ya-review-form h3,
    .ya-ebc-page .ya-related-head h2,
    .ya-ebc-page .ya-related-copy h3,
    .ya-ebc-page .ya-gallery-label strong{font-family:"Newsreader",Georgia,serif}

    .ya-ebc-page .ya-inner{width:min(1240px,calc(100% - 80px))}
    .ya-ebc-page .ya-kicker{color:var(--gold);font-size:10px;letter-spacing:2.3px}
    .ya-ebc-page .ya-kicker:before{width:28px}
    .ya-ebc-page .ya-btn{min-height:50px;border-radius:2px;padding:0 24px;font-size:10px;letter-spacing:1.45px}
    .ya-ebc-page .ya-btn-primary{background:var(--green-2)}
    .ya-ebc-page .ya-btn-primary:hover{background:#4f6246}
    .ya-ebc-page .ya-btn-light{color:var(--green);background:#fff}
    .ya-ebc-page .ya-btn-light:hover{background:var(--gold);color:var(--green)}
    .ya-ebc-page .ya-btn-outline{border-color:rgba(255,255,255,.7);background:transparent}
    .ya-ebc-page .ya-btn-outline:hover{background:#fff;color:var(--green)}

    .ya-ebc-page .ya-hero{
      min-height:98vh;min-height:98svh;padding:170px 0 112px;align-items:flex-end;
      background-position:center;background-color:#252b25;
    }
    .ya-ebc-page .ya-hero:before{background:linear-gradient(90deg,rgba(5,10,7,.72) 0%,rgba(5,10,7,.37) 56%,rgba(5,10,7,.16) 100%)}
    .ya-ebc-page .ya-hero-content{max-width:820px}
    .ya-ebc-page .ya-breadcrumbs{margin-bottom:38px;font-size:9px;letter-spacing:1.5px}
    .ya-ebc-page .ya-hero .ya-script{margin-bottom:14px;font-size:clamp(34px,4.1vw,58px);font-weight:500}
    .ya-ebc-page .ya-hero h1{max-width:820px;font-size:clamp(57px,7vw,104px);line-height:.88;letter-spacing:-.045em}
    .ya-ebc-page .ya-hero-subtitle{max-width:650px;margin:30px 0 36px;font-size:15px;line-height:1.9}
    .ya-ebc-page .ya-hero-badge{width:154px;height:154px;right:36px;background:var(--green);border-radius:0;box-shadow:none}
    .ya-ebc-page .ya-hero-badge strong{font-family:"Newsreader",Georgia,serif;color:var(--gold);font-size:48px}

    .ya-ebc-page .ya-panel{padding:90px 0;background:#fff}
    .ya-ebc-page .ya-panel:nth-child(even){background:var(--cream)}
    .ya-ebc-page .ya-section-heading{max-width:760px;margin:0 0 48px}
    .ya-ebc-page .ya-section-heading .ya-script{font-size:clamp(36px,4.2vw,58px);line-height:.95;color:var(--green-2)}
    .ya-ebc-page .ya-section-heading h2{margin-top:13px;font-size:clamp(42px,5vw,68px);line-height:.95;letter-spacing:-.03em;color:var(--ink)}
    .ya-ebc-page .ya-section-heading p{max-width:640px;margin-top:20px;font-size:15px;line-height:1.9;color:#596359}

    .ya-ebc-page .ya-btn-gold{background:var(--gold);color:var(--green);font-weight:700;border:0}
    .ya-ebc-page .ya-btn-gold:hover{background:#c49f45;color:var(--green)}
    .ya-ebc-page .ya-btn-dark{background:var(--green);color:#fff;border:0;font-size:11px;font-weight:700;letter-spacing:1px;padding:12px 22px;border-radius:4px;display:inline-flex;align-items:center;text-decoration:none}
    .ya-ebc-page .ya-btn-dark:hover{background:var(--green-2);color:#fff}
    .ya-ebc-page .ya-btn-sm{padding:7px 14px;font-size:11px;font-weight:600;border:1px solid var(--line);background:#fff;border-radius:4px;cursor:pointer;color:var(--ink);transition:all .2s ease}
    .ya-ebc-page .ya-btn-sm:hover{background:var(--cream);border-color:#b5b5aa}

    /* Overview Section: 2-Column with Checklist & Trip Facts Card */
    .ya-ebc-page .ya-overview-grid{display:grid;grid-template-columns:1fr 340px;gap:48px;align-items:start}
    .ya-ebc-page .ya-overview-copy h3{font-size:clamp(26px,2.7vw,38px);line-height:1.15;color:var(--ink);margin-bottom:20px}
    .ya-ebc-page .ya-overview-text{font-size:15px;line-height:1.88;color:#4f564e}

    .ya-ebc-page .ya-inclusions-checklist{margin:32px 0 28px;display:flex;flex-direction:column;gap:14px}
    .ya-ebc-page .ya-check-item{display:flex;align-items:flex-start;gap:14px}
    .ya-ebc-page .ya-check-icon{
      flex-shrink:0;
      width:22px;
      height:22px;
      border-radius:50%;
      background:#e8f4ed;
      color:#1e7e4d;
      display:grid;
      place-items:center;
      margin-top:2px;
    }
    .ya-ebc-page .ya-check-icon svg{width:13px;height:13px}
    .ya-ebc-page .ya-check-content strong{display:block;font-size:14px;color:var(--ink);line-height:1.4}
    .ya-ebc-page .ya-check-desc{font-size:13px;color:#636b62;margin-top:2px}

    .ya-ebc-page .ya-overview-actions{margin-top:28px;display:flex;gap:12px;flex-wrap:wrap}

    /* Right Side: Scenic Photo + Trip Facts Card */
    .ya-ebc-page .ya-overview-side{display:flex;flex-direction:column;gap:20px}
    .ya-ebc-page .ya-side-scenic-photo{
      border-radius:6px;
      overflow:hidden;
      box-shadow:0 8px 24px rgba(0,0,0,0.06);
      height:210px;
    }
    .ya-ebc-page .ya-side-scenic-photo img{width:100%;height:100%;object-fit:cover}

    .ya-ebc-page .ya-trip-facts-card{
      background:#fff;
      border:1px solid var(--line);
      border-radius:6px;
      padding:24px;
      box-shadow:0 6px 20px rgba(0,0,0,0.03);
    }
    .ya-ebc-page .ya-trip-facts-card h4{
      margin:0 0 16px;
      font-size:12px;
      font-weight:800;
      letter-spacing:1.8px;
      color:var(--ink);
      border-bottom:1px solid var(--line);
      padding-bottom:10px;
    }
    .ya-ebc-page .ya-trip-facts-list{list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:12px}
    .ya-ebc-page .ya-trip-facts-list li{
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:12px;
      font-size:13px;
      border-bottom:1px dashed #eae7df;
      padding-bottom:10px;
    }
    .ya-ebc-page .ya-trip-facts-list li:last-child{border-bottom:0;padding-bottom:0}
    .ya-ebc-page .ya-tf-label{display:flex;align-items:center;gap:8px;color:#788077;font-size:12px}
    .ya-ebc-page .ya-tf-label svg{width:15px;height:15px;color:var(--gold)}
    .ya-ebc-page .ya-tf-val{font-size:13px;color:var(--ink);font-weight:600;text-align:right}

    /* Highlight Quote Banner */
    .ya-ebc-page .ya-quote-banner{
      margin:48px 0 44px;
      padding:28px 36px;
      background:#fbf7ec;
      border-left:4px solid var(--gold);
      border-radius:4px;
      text-align:center;
    }
    .ya-ebc-page .ya-quote-banner blockquote{
      margin:0;
      font-family:"Newsreader",Georgia,serif;
      font-size:clamp(18px,2vw,22px);
      line-height:1.55;
      color:#2b332b;
      font-style:italic;
    }

    /* Roadmap Section */
    .ya-ebc-page .ya-roadmap-section{
      margin-top:40px;
      background:#fff;
      border:1px solid var(--line);
      border-radius:6px;
      padding:26px;
      box-shadow:0 8px 24px rgba(0,0,0,0.03);
    }
    .ya-ebc-page .ya-roadmap-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;flex-wrap:wrap;gap:14px}
    .ya-ebc-page .ya-roadmap-header h3{margin:4px 0 0;font:600 24px/1.2 "Newsreader",Georgia,serif;color:var(--ink)}
    .ya-ebc-page .ya-roadmap-frame{
      position:relative;
      border-radius:4px;
      overflow:hidden;
      background:#eee;
      max-height:540px;
    }
    .ya-ebc-page .ya-roadmap-frame img{width:100%;height:auto;display:block;object-fit:cover}
    .ya-ebc-page .ya-roadmap-caption{
      display:flex;
      align-items:center;
      gap:8px;
      margin-top:14px;
      font-size:12.5px;
      color:#666e65;
    }
    .ya-ebc-page .ya-roadmap-caption svg{width:16px;height:16px;color:var(--gold);flex-shrink:0}

    /* Detailed Itinerary with Day Photos */
    .ya-ebc-page .ya-itin-head-row{display:flex;align-items:flex-end;justify-content:space-between;flex-wrap:wrap;gap:16px;margin-bottom:20px}
    .ya-ebc-page .ya-itin-actions{display:flex;gap:10px}
    .ya-ebc-page .ya-itinerary-shell{width:100%}
    .ya-ebc-page .ya-days{display:flex;flex-direction:column;gap:16px}
    .ya-ebc-page .ya-day{
      border:1px solid var(--line);
      border-radius:6px;
      background:#fff;
      box-shadow:0 3px 14px rgba(0,0,0,0.02);
      overflow:hidden;
      transition:box-shadow .2s ease;
    }
    .ya-ebc-page .ya-day:hover{box-shadow:0 8px 24px rgba(0,0,0,0.06)}
    .ya-ebc-page .ya-day-card-grid{display:grid;grid-template-columns:1fr 240px;gap:20px;padding:24px}
    .ya-ebc-page .ya-day-main{display:flex;flex-direction:column}
    .ya-ebc-page .ya-day-head{
      padding:0;
      background:transparent;
      border:0;
      width:100%;
      display:flex;
      justify-content:space-between;
      align-items:flex-start;
      gap:14px;
      text-align:left;
      cursor:pointer;
    }
    .ya-ebc-page .ya-day-title-block{display:flex;flex-direction:column;gap:4px}
    .ya-ebc-page .ya-day-tag{font-size:11px;font-weight:800;letter-spacing:1.5px;color:var(--gold);text-transform:uppercase}
    .ya-ebc-page .ya-day-title-block strong{font-size:22px;color:var(--ink);line-height:1.25}
    .ya-ebc-page .ya-day-toggle{width:32px;height:32px;border-radius:50%;background:#f4f3ec;color:var(--green);border:1px solid var(--line);display:grid;place-items:center;flex-shrink:0}
    .ya-ebc-page .ya-day.is-open .ya-day-toggle{background:var(--green-2);border-color:var(--green-2);color:#fff}
    .ya-ebc-page .ya-day-body{display:grid;grid-template-rows:0fr;transition:grid-template-rows .35s ease}
    .ya-ebc-page .ya-day.is-open .ya-day-body{grid-template-rows:1fr}
    .ya-ebc-page .ya-day-body-inner{overflow:hidden}
    .ya-ebc-page .ya-day-desc{padding:14px 0 10px;color:#555d55;font-size:14.5px;line-height:1.8}
    .ya-ebc-page .ya-day-desc p{margin:0 0 12px}
    .ya-ebc-page .ya-day-meta{display:flex;flex-wrap:wrap;gap:8px;margin-top:10px}
    .ya-ebc-page .ya-meta-pill{border-radius:4px;padding:5px 11px;background:#f3f1ea;color:#454c44;font-size:11px;font-weight:600;border:1px solid #e0ddd3}
    .ya-ebc-page .ya-day-photo{border-radius:6px;overflow:hidden;height:165px;background:#eee}
    .ya-ebc-page .ya-day-photo img{width:100%;height:100%;object-fit:cover;transition:transform .4s ease}
    .ya-ebc-page .ya-day:hover .ya-day-photo img{transform:scale(1.04)}

    /* Essential Info Accordion */
    .ya-ebc-page .ya-essential-layout{display:grid;grid-template-columns:1fr 340px;gap:44px}
    .ya-ebc-page .ya-info-list{display:flex;flex-direction:column;gap:12px}
    .ya-ebc-page .ya-info-item{border:1px solid var(--line);border-radius:6px;background:#fff;overflow:hidden}
    .ya-ebc-page .ya-info-head{padding:18px 22px;background:#fff;border:0;width:100%;display:flex;justify-content:space-between;align-items:center;cursor:pointer}
    .ya-ebc-page .ya-info-head span{font-size:17px;font-weight:600;color:var(--ink);display:flex;align-items:center;gap:12px}
    .ya-ebc-page .ya-info-icon{width:22px;height:22px;display:grid;place-items:center;color:var(--green-2)}
    .ya-ebc-page .ya-info-arrow{width:18px;height:18px;color:#788077;transition:transform .3s ease}
    .ya-ebc-page .ya-info-item.is-open .ya-info-arrow{transform:rotate(180deg)}
    .ya-ebc-page .ya-info-body{display:grid;grid-template-rows:0fr;transition:grid-template-rows .35s ease}
    .ya-ebc-page .ya-info-item.is-open .ya-info-body{grid-template-rows:1fr}
    .ya-ebc-page .ya-info-content{padding:0 22px 20px 56px;color:#555e55;font-size:14.5px;line-height:1.8}
    .ya-ebc-page .ya-trip-note-card{border-radius:6px;background:var(--green);color:#fff;overflow:hidden;box-shadow:0 10px 30px rgba(0,0,0,0.1)}
    .ya-ebc-page .ya-trip-note-image{height:220px}
    .ya-ebc-page .ya-trip-note-copy{padding:28px}

    /* Traveler Reviews Grid */
    .ya-ebc-page .ya-rev-head-row{display:flex;justify-content:space-between;align-items:flex-end;flex-wrap:wrap;gap:16px;margin-bottom:28px}
    .ya-ebc-page .ya-review-cards{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:22px}
    .ya-ebc-page .ya-review-card{
      display:flex;
      flex-direction:column;
      justify-content:space-between;
      padding:28px 24px;
      border:1px solid var(--line);
      border-radius:6px;
      background:#fff;
      box-shadow:0 4px 18px rgba(0,0,0,.03);
      transition:transform .25s ease,box-shadow .25s ease;
    }
    .ya-ebc-page .ya-review-card:hover{transform:translateY(-3px);box-shadow:0 12px 30px rgba(0,0,0,.06)}
    .ya-ebc-page .ya-rev-card-top{margin-bottom:14px}
    .ya-ebc-page .ya-review-card .ya-stars{display:flex;gap:3px;color:var(--gold)}
    .ya-ebc-page .ya-review-card .ya-stars svg{width:16px;height:16px;fill:currentColor}
    .ya-ebc-page .ya-review-card blockquote{
      font-size:15.5px;
      line-height:1.6;
      color:var(--ink);
      font-family:"Newsreader",Georgia,serif;
      font-style:italic;
      margin:0 0 22px;
      flex-grow:1;
    }
    .ya-ebc-page .ya-rev-author{display:flex;align-items:center;gap:12px}
    .ya-ebc-page .ya-rev-avatar{
      width:38px;
      height:38px;
      border-radius:50%;
      color:#fff;
      font-size:13px;
      font-weight:700;
      display:grid;
      place-items:center;
      flex-shrink:0;
    }
    .ya-ebc-page .ya-rev-author strong{display:block;font-size:13.5px;color:var(--ink)}
    .ya-ebc-page .ya-rev-author small{display:block;font-size:12px;color:#7e857c}

    .ya-ebc-page .ya-rev-form-wrap{margin-top:54px}
    .ya-ebc-page .ya-review-form{
      max-width:860px;
      width:100%;
      margin:0 auto;
      padding:40px;
      border:1px solid var(--line);
      border-radius:6px;
      background:#fff;
      box-shadow:0 6px 24px rgba(0,0,0,.03);
    }
    .ya-ebc-page .ya-review-form h3{font-size:28px;color:var(--ink);margin:0 0 8px}

    /* Dark CTA Section ("Ready for the Himalaya?") */
    .ya-ebc-page .ya-dark-cta-section{
      background:linear-gradient(135deg,#131d17 0%,#0c140f 100%);
      color:#fff;
      padding:84px 0;
      text-align:center;
    }
    .ya-ebc-page .ya-dark-cta-inner{max-width:800px;margin:0 auto}
    .ya-ebc-page .ya-dark-cta-heading{
      margin:0;
      font-family:"Newsreader",Georgia,serif;
      font-size:clamp(36px,4.5vw,56px);
      font-style:italic;
      color:#e8be61;
      line-height:1.15;
    }
    .ya-ebc-page .ya-dark-cta-sub{
      margin:16px auto 34px;
      max-width:620px;
      color:rgba(255,255,255,0.85);
      font-size:15px;
      line-height:1.75;
    }
    .ya-ebc-page .ya-dark-cta-form{
      display:flex;
      justify-content:center;
      max-width:480px;
      margin:0 auto 28px;
      gap:8px;
    }
    .ya-ebc-page .ya-dark-cta-form input{
      flex-grow:1;
      height:48px;
      border-radius:4px;
      border:1px solid rgba(255,255,255,0.25);
      background:rgba(255,255,255,0.08);
      color:#fff;
      padding:0 16px;
      font-size:14px;
    }
    .ya-ebc-page .ya-dark-cta-form input::placeholder{color:rgba(255,255,255,0.5)}
    .ya-ebc-page .ya-dark-cta-form .ya-btn{
      padding:0 24px;
      min-height:48px;
      border-radius:4px;
      font-size:12px;
      white-space:nowrap;
    }
    .ya-ebc-page .ya-dark-cta-badges{
      display:flex;
      justify-content:center;
      align-items:center;
      gap:28px;
      flex-wrap:wrap;
      font-size:13px;
      color:rgba(255,255,255,0.72);
    }
    .ya-ebc-page .ya-dark-cta-badges span{display:inline-flex;align-items:center;gap:6px}
    .ya-ebc-page .ya-dark-cta-badges svg{width:16px;height:16px;color:#e8be61}

    /* Related Journeys 3-Card Grid */
    .ya-ebc-page .ya-related{padding:100px 0;background:#f7f6f2;border-top:1px solid var(--line)}
    .ya-ebc-page .ya-related-grid{grid-template-columns:repeat(3,minmax(0,1fr));gap:24px}
    .ya-ebc-page .ya-related-card{border-radius:4px;overflow:hidden;background:#fff;border:1px solid var(--line);box-shadow:0 4px 20px rgba(0,0,0,0.03);transition:transform .3s ease,box-shadow .3s ease}
    .ya-ebc-page .ya-related-card:hover{transform:translateY(-4px);box-shadow:0 14px 34px rgba(0,0,0,0.08)}
    .ya-ebc-page .ya-related-image{height:250px;overflow:hidden}
    .ya-ebc-page .ya-related-image img{width:100%;height:100%;object-fit:cover;transition:transform .5s ease}
    .ya-ebc-page .ya-related-card:hover .ya-related-image img{transform:scale(1.05)}
    .ya-ebc-page .ya-related-copy{padding:22px}
    .ya-ebc-page .ya-related-copy small{display:block;font-size:11px;font-weight:700;color:var(--gold);letter-spacing:1.2px;text-transform:uppercase;margin-bottom:8px}
    .ya-ebc-page .ya-related-copy h3{font-size:22px;line-height:1.22;color:var(--ink);margin:0 0 14px}
    .ya-ebc-page .ya-related-link{display:inline-flex;align-items:center;gap:8px;font-size:11px;font-weight:700;color:var(--green-2);letter-spacing:1.2px;text-transform:uppercase}
    .ya-ebc-page .ya-related-link svg{width:14px;height:14px;transition:transform .2s ease}
    .ya-ebc-page .ya-related-card:hover .ya-related-link svg{transform:translateX(4px)}

    @media(max-width:1100px){
      .ya-ebc-page .ya-inner{width:min(100% - 56px,1240px)}
      .ya-ebc-page .ya-facts-grid{grid-template-columns:repeat(3,1fr)}
      .ya-ebc-page .ya-overview-grid{grid-template-columns:1fr}
      .ya-ebc-page .ya-essential-layout{grid-template-columns:1fr}
      .ya-ebc-page .ya-review-cards{grid-template-columns:repeat(2,1fr)}
      .ya-ebc-page .ya-day-card-grid{grid-template-columns:1fr 200px}
      .ya-ebc-page .ya-essential-layout,.ya-ebc-page .ya-review-layout{gap:42px}
    }
    @media(max-width:820px){
      .ya-ebc-page .ya-inner{width:min(100% - 32px,1240px)}
      .ya-ebc-page .ya-hero{min-height:92svh;padding:135px 0 70px}
      .ya-ebc-page .ya-hero:before{background:linear-gradient(0deg,rgba(4,9,6,.75),rgba(4,9,6,.25))}
      .ya-ebc-page .ya-hero h1{font-size:clamp(52px,13vw,76px)}
      .ya-ebc-page .ya-facts-grid{grid-template-columns:repeat(2,1fr)}
      .ya-ebc-page .ya-fact{min-height:112px;padding:18px 12px;border-top:1px solid var(--line)}
      .ya-ebc-page .ya-fact:nth-child(odd){border-right:1px solid var(--line)!important}
      .ya-ebc-page .ya-fact:nth-child(even){border-right:0!important}
      .ya-ebc-page .ya-fact:first-child,.ya-ebc-page .ya-fact:nth-child(2){border-top:0}
      .ya-ebc-page .ya-panel{padding:88px 0}
      .ya-ebc-page .ya-section-heading{margin-bottom:42px}
      .ya-ebc-page .ya-overview-grid,.ya-ebc-page .ya-itinerary-shell,.ya-ebc-page .ya-essential-layout,.ya-ebc-page .ya-review-layout{grid-template-columns:1fr;gap:42px}
      .ya-ebc-page .ya-review-cards{grid-template-columns:1fr}
      .ya-ebc-page .ya-day-content{padding-left:0}
      .ya-ebc-page .ya-gallery-grid{grid-template-columns:1fr 1fr;grid-template-rows:390px 260px 260px}
      .ya-ebc-page .ya-gallery-item:first-child{grid-column:1/3;grid-row:auto}
      .ya-ebc-page .ya-related-grid{grid-template-columns:repeat(2,1fr)}
    }
    @media(max-width:560px){
      .ya-ebc-page .ya-hero-actions{display:grid;gap:10px}
      .ya-ebc-page .ya-btn{width:100%}
      .ya-ebc-page .ya-fact-icon{display:none}
      .ya-ebc-page .ya-fact strong{font-size:12px}
      .ya-ebc-page .ya-tab{padding:0 15px}
      .ya-ebc-page .ya-tab:after{right:15px;left:15px}
      .ya-ebc-page .ya-map-image{height:430px}
      .ya-ebc-page .ya-day-head{grid-template-columns:50px minmax(0,1fr) 34px;gap:12px}
      .ya-ebc-page .ya-day-title strong{font-size:23px}
      .ya-ebc-page .ya-day-number{font-size:22px}
      .ya-ebc-page .ya-gallery-grid{grid-template-columns:1fr;grid-template-rows:none}
      .ya-ebc-page .ya-gallery-item,.ya-ebc-page .ya-gallery-item:first-child{grid-column:auto;height:300px}
      .ya-ebc-page .ya-related-grid{grid-template-columns:1fr}
    }

    /* Long-form editorial reading: all existing panels remain in the scrolling story. */
    .ya-ebc-page .ya-tab-stage .ya-panel{
      display:block;
      scroll-margin-top:112px;
    }
    .ya-ebc-page .ya-tab-stage .ya-panel.is-active{animation:none}
    .ya-ebc-page .ya-reading-progress{--ya-reading-progress:0%;height:3px;margin-top:18px;overflow:hidden;background:#dcdcd3}
    .ya-ebc-page .ya-reading-progress-bar{display:block;width:var(--ya-reading-progress);height:100%;background:#d1ad54;transition:width .18s linear,height .18s linear}

    /* Long-form editorial reading: all existing panels remain in the scrolling story. */
    .ya-ebc-page .ya-tab-stage .ya-panel{
      display:block;
      scroll-margin-top:112px;
    }
    .ya-ebc-page .ya-tab-stage .ya-panel.is-active{animation:none}

    /* Centred destination hero with a CPT-managed, cross-fading image slideshow. */
    .ya-ebc-page .ya-hero{
      min-height:100vh;
      min-height:100svh;
      padding:0;
      overflow:hidden;
      background-image:none!important;
    }
    .ya-ebc-page .ya-section-heading h2{font-size:52px}
    .ya-ebc-page .ya-hero > .ya-inner{
      width:min(1400px,calc(100% - 80px));
      min-height:inherit;
      display:flex;
      align-items:center;
      justify-content:center;
    }
    .ya-ebc-page .ya-hero-background,
    .ya-ebc-page .ya-hero-background::after,
    .ya-ebc-page .ya-hero-background-slide{
      position:absolute;
      inset:0;
    }
    .ya-ebc-page .ya-hero-background{z-index:-2;background:#252b25}
    .ya-ebc-page .ya-hero-background::after{
      z-index:1;
      content:"";
      background:linear-gradient(180deg,rgba(4,10,7,.54) 0%,rgba(4,10,7,.4) 45%,rgba(4,10,7,.65) 100%);
    }
    .ya-ebc-page .ya-hero-background-slide{
      z-index:0;
      opacity:0;
      background:center / cover no-repeat;
      transform:translate3d(4%,0,0) scale(1.04);
      transition:opacity 950ms ease,transform 1200ms cubic-bezier(.22,.61,.36,1);
    }
    .ya-ebc-page .ya-hero-background-slide.is-active{opacity:1;transform:translate3d(0,0,0) scale(1.02)}
    .ya-ebc-page .ya-hero-background-slide.is-exiting{opacity:0;transform:translate3d(-4%,0,0) scale(1.02)}
    .ya-ebc-page .ya-hero-slider-dots{
      position:absolute;
      z-index:3;
      bottom:30px;
      left:50%;
      display:flex;
      align-items:center;
      justify-content:center;
      gap:9px;
      transform:translateX(-50%);
    }
    .ya-ebc-page .ya-hero-slider-dot{
      width:8px;
      height:8px;
      margin:0;
      padding:0;
      border:1px solid rgba(255,255,255,.92);
      border-radius:999px;
      background:rgba(255,255,255,.18);
      box-shadow:0 2px 10px rgba(0,0,0,.3);
      cursor:pointer;
      transition:width .3s ease,background .3s ease,transform .3s ease;
    }
    .ya-ebc-page .ya-hero-slider-dot:hover{transform:scale(1.2)}
    .ya-ebc-page .ya-hero-slider-dot.is-active{width:30px;background:#fff}
    .ya-ebc-page .ya-hero-slider-dot:focus-visible{outline:2px solid #fff;outline-offset:4px}

    /* Brand interaction system: sage #617654, gold #d1ad54. */
    .ya-ebc-page .ya-btn{
      border-color:#617654;
      background:#617654;
      color:#fff;
      box-shadow:0 8px 20px rgba(32,40,33,.18);
      transition:transform .22s ease,background .22s ease,color .22s ease,border-color .22s ease,box-shadow .22s ease;
    }
    .ya-ebc-page .ya-btn-light,
    .ya-ebc-page .ya-btn-secondary{border-color:#617654;background:#fff;color:#617654}
    .ya-ebc-page .ya-btn-outline{border-color:#d1ad54;background:transparent;color:#fff}
    .ya-ebc-page .ya-btn:hover,
    .ya-ebc-page .ya-btn:focus-visible{
      border-color:#d1ad54;
      background:#d1ad54;
      color:#202821;
      box-shadow:0 12px 25px rgba(209,173,84,.32);
      transform:translateY(-3px);
    }
    .ya-ebc-page .ya-btn:active{background:#617654;color:#fff;box-shadow:0 3px 9px rgba(32,40,33,.22);transform:translateY(1px) scale(.98)}
    .ya-ebc-page .ya-btn:focus-visible{outline:2px solid #617654;outline-offset:3px}

    .ya-ebc-page .ya-tab{transition:background .22s ease,color .22s ease,transform .22s ease}
    .ya-ebc-page .ya-tab:hover{background:rgba(209,173,84,.14);color:#617654}
    .ya-ebc-page .ya-tab.is-active{background:#617654;color:#fff}
    .ya-ebc-page .ya-tab:active{background:#d1ad54;color:#202821;transform:scale(.98)}
    .ya-ebc-page .ya-tab:focus-visible{outline:2px solid #d1ad54;outline-offset:-3px}

    .ya-ebc-page .ya-day-head,
    .ya-ebc-page .ya-info-head{transition:background .22s ease,color .22s ease}
    .ya-ebc-page .ya-day-head:hover,
    .ya-ebc-page .ya-info-head:hover{background:rgba(209,173,84,.12);color:#617654}
    .ya-ebc-page .ya-day-head:active,
    .ya-ebc-page .ya-info-head:active{background:rgba(97,118,84,.16);color:#202821}
    .ya-ebc-page .ya-day-head:focus-visible,
    .ya-ebc-page .ya-info-head:focus-visible{outline:2px solid #d1ad54;outline-offset:-3px}

    .ya-ebc-page .ya-hero-slider-dot{border-color:#d1ad54;background:rgba(209,173,84,.28)}
    .ya-ebc-page .ya-hero-slider-dot:hover,
    .ya-ebc-page .ya-hero-slider-dot.is-active{background:#d1ad54}
    .ya-ebc-page .ya-hero-slider-dot:active{background:#617654;transform:scale(.9)}
    .ya-ebc-page .ya-lightbox-close{transition:background .22s ease,color .22s ease,border-color .22s ease,transform .22s ease}
    .ya-ebc-page .ya-lightbox-close:hover{border-color:#d1ad54;background:#d1ad54;color:#202821;transform:rotate(90deg)}
    .ya-ebc-page .ya-lightbox-close:active{background:#617654;color:#fff;transform:rotate(90deg) scale(.92)}
    .ya-ebc-page .ya-hero > .ya-inner > .ya-breadcrumbs{
      position:absolute;
      top:clamp(110px,1vw,170px);
      left:0;
      z-index:1;
      margin:0;
    }
    .ya-ebc-page .ya-hero-content{
      width:min(820px,100%);
      max-width:none;
      display:flex;
      flex-direction:column;
      align-items:center;
      color:#fff;
      text-align:center;
      gap:2em;
    }
    .ya-ebc-page .ya-hero .ya-script{font-size:clamp(30px,3.2vw,46px)}
    .ya-ebc-page .ya-hero h1{
      max-width:1100px;
      font-size:56px;
      line-height:.95;
    }
    .ya-ebc-page .ya-hero-subtitle{margin:24px auto 30px}
    .ya-ebc-page .ya-hero-actions{justify-content:center}
    .ya-ebc-page .ya-breadcrumbs [aria-current="page"]{color:#fff}

    @media(max-width:820px){
      .ya-ebc-page .ya-hero{min-height:100vh;min-height:100svh}
      .ya-ebc-page .ya-hero > .ya-inner{width:min(100% - 32px,1400px)}
      .ya-ebc-page .ya-hero > .ya-inner > .ya-breadcrumbs{top:112px}
      .ya-ebc-page .ya-hero h1{font-size:clamp(40px,10vw,56px)}
      .ya-ebc-page .ya-hero-subtitle{font-size:14px}
    }
    @media(max-width:560px){
      .ya-ebc-page .ya-hero{min-height:100vh;min-height:100svh}
      .ya-ebc-page .ya-hero > .ya-inner > .ya-breadcrumbs{top:98px;gap:5px;font-size:8px;letter-spacing:1px}
      .ya-ebc-page .ya-breadcrumbs svg{width:11px;height:11px}
      .ya-ebc-page .ya-hero .ya-script{font-size:30px}
      .ya-ebc-page .ya-hero h1{font-size:clamp(38px,11vw,48px)}
      .ya-ebc-page .ya-hero-subtitle{margin:18px auto 25px;line-height:1.7}
      .ya-ebc-page .ya-hero-slider-dots{bottom:20px}
    }
    @media(prefers-reduced-motion:reduce){
      .ya-ebc-page .ya-hero-background-slide{transition:none}
    }

    /* Trip information: one vertical content flow with compact display type. */
    .ya-ebc-page .ya-tab-stage .ya-overview-grid,
    .ya-ebc-page .ya-tab-stage .ya-itinerary-shell,
    .ya-ebc-page .ya-tab-stage .ya-gallery-grid,
    .ya-ebc-page .ya-tab-stage .ya-essential-layout,
    .ya-ebc-page .ya-tab-stage .ya-review-layout,
    .ya-ebc-page .ya-tab-stage .ya-form-grid{
      grid-template-columns:minmax(0,1fr);
    }
    .ya-ebc-page .ya-tab-stage .ya-gallery-grid{grid-template-rows:none}
    .ya-ebc-page .ya-tab-stage .ya-gallery-item:first-child{grid-column:auto;grid-row:auto}
    .ya-ebc-page .ya-tab-stage .ya-map-card,
    .ya-ebc-page .ya-tab-stage .ya-itinerary-side,
    .ya-ebc-page .ya-tab-stage .ya-trip-note-card{position:static}
    .ya-ebc-page .ya-tab-stage .ya-section-heading h2,
    .ya-ebc-page .ya-tab-stage .ya-overview-copy h3,
    .ya-ebc-page .ya-tab-stage .ya-itinerary-side h3,
    .ya-ebc-page .ya-tab-stage .ya-trip-note-copy h3,
    .ya-ebc-page .ya-tab-stage .ya-review-form h3{
      font-size:clamp(28px,3vw,36px);
    }
    .ya-ebc-page .ya-tab-stage .ya-map-caption h3,
    .ya-ebc-page .ya-tab-stage .ya-day-title strong,
    .ya-ebc-page .ya-tab-stage .ya-info-head span,
    .ya-ebc-page .ya-tab-stage .ya-review-card blockquote{
      font-size:min(30px,8vw);
    }

    @media(min-width:1101px){
      .ya-ebc-page .ya-tab-mman-section{
        display:grid;
        grid-template-columns:250px minmax(0,1400px);
        column-gap:48px;
        align-items:flex-start;
        width:min(calc(100% - 80px),1400px);
        margin:0 auto;
      }
      .ya-ebc-page .ya-tabs-wrap{
        position:sticky;
        top:96px;
        z-index:70;
        grid-column:1;
        width:250px;
        background:#ffffff;
        border:1px solid #edf0ea;
        border-radius:20px;
        box-shadow:0 4px 24px rgba(0,0,0,0.04);
        padding:18px 14px;
        margin:0;
        align-self:start;
        float:none;
        overflow:visible;
        box-sizing:border-box;
      }
      .ya-ebc-page .ya-nav-title{
        font-size:11px;
        font-weight:800;
        letter-spacing:0.1em;
        text-transform:uppercase;
        color:#9ca3af;
        padding:4px 12px 14px;
        border-bottom:1px solid #f1f3f0;
        margin-bottom:12px;
      }
      .ya-ebc-page .ya-tabs{
        width:100%;
        display:flex;
        flex-direction:column;
        gap:4px;
        border:0;
        overflow:visible;
      }
      .ya-ebc-page .ya-tab{
        width:100%;
        min-height:auto;
        display:flex;
        align-items:center;
        justify-content:flex-start;
        padding:11px 16px;
        border:0;
        border-radius:10px;
        background:transparent;
        color:#4b5563;
        font-size:14.5px;
        font-weight:600;
        text-align:left;
        text-transform:none;
        letter-spacing:normal;
        cursor:pointer;
        transition:all .2s ease;
        position:relative;
        box-sizing:border-box;
      }
      .ya-ebc-page .ya-tab svg{display:none}
      .ya-ebc-page .ya-tab:hover{
        background:#f9fafb;
        color:#111827;
      }
      .ya-ebc-page .ya-tab.is-active{
        background:#fdfaf3;
        color:#b45309;
        font-weight:700;
      }
      .ya-ebc-page .ya-tab.is-active::before{
        content:"";
        position:absolute;
        left:0;
        top:6px;
        bottom:6px;
        width:4px;
        background:#f59e0b;
        border-radius:0 4px 4px 0;
      }
      .ya-ebc-page .ya-tab:after{display:none!important}

      /* Private Heli Card in Sticky Left Sidebar */
      .ya-ebc-page .ya-heli-card{
        margin-top:18px;
        padding:18px 16px;
        background:#181715;
        border-radius:14px;
        color:#ffffff;
        box-shadow:0 8px 24px rgba(0,0,0,0.12);
      }
      .ya-ebc-page .ya-heli-tag{
        display:flex;
        align-items:center;
        gap:6px;
        font-size:11px;
        font-weight:800;
        letter-spacing:0.08em;
        color:#f59e0b;
        margin-bottom:10px;
        text-transform:uppercase;
      }
      .ya-ebc-page .ya-heli-tag svg{
        width:15px;
        height:15px;
        fill:currentColor;
        flex-shrink:0;
      }
      .ya-ebc-page .ya-heli-card p{
        margin:0 0 14px;
        font-size:12.5px;
        line-height:1.55;
        color:#d1d5db;
      }
      .ya-ebc-page .ya-heli-btn{
        width:100%;
        background:#f59e0b;
        color:#111827;
        font-size:13px;
        font-weight:700;
        border-radius:8px;
        border:0;
        padding:10px 14px;
        text-align:center;
        cursor:pointer;
        transition:all .2s ease;
        box-shadow:0 2px 8px rgba(245,158,11,0.25);
        display:block;
        box-sizing:border-box;
      }
      .ya-ebc-page .ya-heli-btn:hover{
        background:#d97706;
        color:#ffffff;
      }

      .ya-ebc-page .ya-tab-stage{
        grid-column:2;
        min-width:0;
        margin-left:0;
      }
      .ya-ebc-page .ya-tab-stage .ya-panel{padding:0 0 60px}
      .ya-ebc-page .ya-tab-stage .ya-inner{width:100%;max-width:1400px}
      .ya-ebc-page .ya-reading-progress{
        --ya-reading-progress:0%;
        position:absolute;
        top:0;
        right:0;
        bottom:0;
        width:3px;
        height:auto;
        margin:0;
      }
      .ya-ebc-page .ya-reading-progress-bar{
        position:absolute;
        right:0;
        bottom:0;
        width:100%;
        height:var(--ya-reading-progress);
        transition:height .18s linear;
      }
    }

    /* Mobile trip navigation becomes an accordion; Overview is open by default. */
    @media(max-width:1100px){
      .ya-ebc-page .ya-tab-mman-section{display:block;width:100%}
      .ya-ebc-page .ya-tabs-wrap{
        position:static;
        width:100%;
        margin:0;
        border:0;
        background:#fff;
        box-shadow:none;
      }
      .ya-ebc-page .ya-tabs{
        width:100%;
        display:block;
        overflow:visible;
        border-top:1px solid var(--line);
      }
      .ya-ebc-page .ya-tab{
        width:100%;
        min-height:63px;
        display:flex;
        justify-content:space-between;
        padding:0 24px;
        border-bottom:1px solid var(--line);
        background:#fff;
        color:var(--ink);
        font:500 18px/1.25 "Newsreader",Georgia,serif;
        letter-spacing:0;
        text-align:left;
        text-transform:none;
      }
      .ya-ebc-page .ya-tab svg{display:none}
      .ya-ebc-page .ya-tab:after{
        position:static;
        width:auto;
        height:auto;
        content:"›";
        background:transparent;
        color:#5a5d56;
        font:400 32px/1 Arial,sans-serif;
        transform:rotate(0deg);
        transition:transform .2s ease;
      }
      .ya-ebc-page .ya-tab.is-active{background:#fff;color:var(--green-2)}
      .ya-ebc-page .ya-tab.is-active:after{background:transparent;transform:rotate(90deg)}
      .ya-ebc-page .ya-reading-progress{display:none}
      .ya-ebc-page .ya-tab-stage{display:none}
      .ya-ebc-page .ya-tab-stage .ya-panel,
      .ya-ebc-page .ya-tabs > .ya-panel{display:none}
      .ya-ebc-page .ya-tab-stage .ya-panel.is-active,
      .ya-ebc-page .ya-tabs > .ya-panel.is-active{
        display:block;
        padding:42px 24px 56px;
        background:var(--cream);
      }
      .ya-ebc-page .ya-tabs > .ya-panel .ya-inner{width:100%;max-width:none}
      .ya-ebc-page .ya-tabs > .ya-panel .ya-overview-grid,
      .ya-ebc-page .ya-tabs > .ya-panel .ya-itinerary-shell,
      .ya-ebc-page .ya-tabs > .ya-panel .ya-gallery-grid,
      .ya-ebc-page .ya-tabs > .ya-panel .ya-essential-layout,
      .ya-ebc-page .ya-tabs > .ya-panel .ya-review-layout,
      .ya-ebc-page .ya-tabs > .ya-panel .ya-review-cards,
      .ya-ebc-page .ya-tabs > .ya-panel .ya-form-grid{grid-template-columns:minmax(0,1fr)}
      .ya-ebc-page .ya-tabs > .ya-panel .ya-gallery-grid{grid-template-rows:none}
      .ya-ebc-page .ya-tabs > .ya-panel .ya-gallery-item:first-child{grid-column:auto;grid-row:auto}
      .ya-ebc-page .ya-tabs > .ya-panel .ya-map-card,
      .ya-ebc-page .ya-tabs > .ya-panel .ya-itinerary-side,
      .ya-ebc-page .ya-tabs > .ya-panel .ya-trip-note-card{position:static}
      .ya-ebc-page .ya-tabs > .ya-panel .ya-section-heading h2,
      .ya-ebc-page .ya-tabs > .ya-panel .ya-overview-copy h3,
      .ya-ebc-page .ya-tabs > .ya-panel .ya-itinerary-side h3,
      .ya-ebc-page .ya-tabs > .ya-panel .ya-trip-note-copy h3,
      .ya-ebc-page .ya-tabs > .ya-panel .ya-review-form h3{font-size:clamp(28px,8vw,36px)}
    }
  </style>

  <!-- 1. Hero Section -->
  <div class="ya-hero">
    <div class="ya-hero-background" aria-hidden="true">
      <div class="ya-hero-background-slide is-active" style="background-image:url('<?php echo esc_url( $hero_slider_images[0] ?? $hero_bg_img ); ?>');"></div>
      <div class="ya-hero-background-slide"></div>
    </div>
    <?php if ( count( $hero_slider_images ) > 1 ) : ?>
      <div class="ya-hero-slider-dots" role="group" aria-label="<?php esc_attr_e( 'Hero image slideshow', 'trek-content-manager' ); ?>">
        <?php foreach ( $hero_slider_images as $slide_index => $hero_slider_image ) : ?>
          <button class="ya-hero-slider-dot<?php echo 0 === $slide_index ? ' is-active' : ''; ?>" type="button" data-hero-slide="<?php echo esc_attr( $slide_index ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Show hero image %d', 'trek-content-manager' ), $slide_index + 1 ) ); ?>" aria-current="<?php echo 0 === $slide_index ? 'true' : 'false'; ?>"></button>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
    <div class="ya-inner">
      <!-- <div class="ya-breadcrumbs" aria-label="Breadcrumb">
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
        <a href="<?php echo esc_url( home_url( '/trips/' ) ); ?>">All-Trips</a>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
        <a href="<?php echo esc_url( home_url( '/' . strtolower( sanitize_title( $breadcrumb_region ) ) . '/' ) ); ?>"><?php echo esc_html( $breadcrumb_region ); ?></a>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
        <span aria-current="page"><?php echo esc_html( $title ); ?></span>
      </div> -->
      <div class="ya-hero-content ya-reveal">
        <div class="ya-script"><?php echo esc_html( $script_title ); ?></div>
        <h1><?php echo esc_html( $hero_title ); ?></h1>

        <div class="ya-hero-actions">
          <button class="ya-btn ya-btn-light ya-jump-tab" type="button" data-target="itinerary">View Itinerary</button>
          <button class="ya-btn ya-btn-outline" type="button" id="ya-open-enquiry-modal">Start Planning Your Trip</button>
        </div>
      </div>
      <div class="ya-hero-badge" aria-hidden="true">
        <div><strong><?php echo esc_html( $hero_days ); ?></strong><span><?php echo esc_html( $hero_badge_sub ); ?></span></div>
      </div>
    </div>
  </div>

  <!-- 2. Quick Facts Grid -->
  <div class="ya-facts">
    <div class="ya-inner ya-facts-grid ya-reveal">
      <div class="ya-fact">
        <span class="ya-fact-icon" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 20L10 6l4 8 2-4 4 10H4z"/></svg>
        </span>
        <div class="ya-fact-text">
          <small>HIGHEST POINT</small>
          <strong><?php echo esc_html( $fact_high_point ); ?></strong>
        </div>
      </div>
      <div class="ya-fact">
        <span class="ya-fact-icon" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 21s7-5.2 7-12a7 7 0 10-14 0c0 6.8 7 12 7 12z"/><circle cx="12" cy="9" r="2.5"/></svg>
        </span>
        <div class="ya-fact-text">
          <small>DESTINATION</small>
          <strong><?php echo esc_html( $fact_region ?: $title ); ?></strong>
        </div>
      </div>
      <div class="ya-fact">
        <span class="ya-fact-icon" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M7 17.5c0-1.5 1-3.5 2.5-4.5S11 11 11 9c0-1.1-.9-2-2-2S7 7.9 7 9"/><path d="M17 17.5c0-1.5-1-3.5-2.5-4.5S13 11 13 9c0-1.1.9-2 2-2s2 .9 2 2"/><circle cx="7" cy="19.5" r="1.5"/><circle cx="17" cy="19.5" r="1.5"/></svg>
        </span>
        <div class="ya-fact-text">
          <small>DAILY WALKING</small>
          <strong><?php echo esc_html( $fact_trekking ); ?></strong>
        </div>
      </div>
      <div class="ya-fact">
        <span class="ya-fact-icon" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 7v11M21 7v11M3 14h18M7 11h4a2 2 0 0 0 2-2V7H7v4z"/></svg>
        </span>
        <div class="ya-fact-text">
          <small>ACCOMMODATION</small>
          <strong><?php echo esc_html( $fact_accom ); ?></strong>
        </div>
      </div>
      <div class="ya-fact">
        <span class="ya-fact-icon" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M9 12l2 2 4-4"/></svg>
        </span>
        <div class="ya-fact-text">
          <small>TRIP STYLE</small>
          <strong><?php echo esc_html( $fact_style ); ?></strong>
        </div>
      </div>
    </div>
  </div>

  <!-- 3. Sticky Navigation Tabs -->
  <section class="ya-tab-mman-section">
    <nav class="ya-tabs-wrap" aria-label="Trip information tabs">
      <div class="ya-nav-title">NAVIGATION</div>
      <div class="ya-inner ya-tabs" role="tablist">
        <button class="ya-tab is-active" type="button" role="tab" aria-selected="true" aria-controls="ya-panel-overview" data-tab="overview">
          Overview
        </button>
        <button class="ya-tab" type="button" role="tab" aria-selected="false" data-tab="overview" data-scroll-target=".ya-inclusions-checklist">
          Highlights
        </button>
        <button class="ya-tab" type="button" role="tab" aria-selected="false" aria-controls="ya-panel-itinerary" data-tab="itinerary">
          Itinerary
        </button>
        <button class="ya-tab" type="button" role="tab" aria-selected="false" aria-controls="ya-panel-essential-info" data-tab="essential-info">
          Essential Info
        </button>
        <button class="ya-tab" type="button" role="tab" aria-selected="false" data-tab="overview" data-scroll-target=".ya-roadmap-section">
          Extensions
        </button>
        <button class="ya-tab" type="button" role="tab" aria-selected="false" aria-controls="ya-panel-review" data-tab="review">
          Reviews
        </button>
      </div>

      <!-- Private Heli Charters Promo Card in Left Sidebar -->
      <div class="ya-heli-card ya-reveal">
        <div class="ya-heli-tag">
          <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2L14.4 7.6L20 10L14.4 12.4L12 18L9.6 12.4L4 10L9.6 7.6L12 2Z"/></svg>
          <span>PRIVATE HELI CHARTERS</span>
        </div>
        <p>Customize this trek with private VIP helicopters and 5-star mountain lodges.</p>
        <button type="button" class="ya-btn ya-heli-btn" onclick="document.querySelector('#ya-open-enquiry-modal')?.click()">Speak to a Specialist</button>
      </div>

      <div class="ya-reading-progress" aria-hidden="true"><span class="ya-reading-progress-bar"></span></div>
    </nav>

    <div class="ya-tab-stage">
      <!-- Panel 1: Overview -->
      <section class="ya-panel is-active" id="ya-panel-overview" role="tabpanel" data-panel="overview">
        <div class="ya-inner">
          <div class="ya-section-heading ya-reveal">
            <span class="ya-kicker"><?php echo esc_html( $overview_kicker ); ?></span>
            <div class="ya-script"><?php echo esc_html( $overview_script ); ?></div>
            <h2>Overview</h2>
          </div>

          <div class="ya-overview-grid">
            <div class="ya-overview-copy ya-reveal">
              <h3><?php echo esc_html( $overview_subheading ); ?></h3>
              <div class="ya-overview-text">
                <?php echo wp_kses_post( wpautop( $overview_content ) ); ?>
              </div>

              <!-- Checklist of Highlights & Inclusions -->
              <div class="ya-inclusions-checklist">
                <?php foreach ( $inclusions as $inc_point ) : 
                  $inc_title = is_array( $inc_point ) ? ( $inc_point['title'] ?? '' ) : $inc_point;
                  $inc_desc  = is_array( $inc_point ) ? ( $inc_point['desc'] ?? '' ) : '';
                  if ( empty( $inc_title ) && empty( $inc_desc ) ) continue;
                ?>
                  <div class="ya-check-item">
                    <span class="ya-check-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 13l4 4L19 7"/></svg></span>
                    <div class="ya-check-content">
                      <?php if ( ! empty( $inc_title ) ) : ?><strong><?php echo esc_html( $inc_title ); ?></strong><?php endif; ?>
                      <?php if ( ! empty( $inc_desc ) ) : ?><div class="ya-check-desc"><?php echo wp_kses_post( wpautop( $inc_desc ) ); ?></div><?php endif; ?>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>

              <div class="ya-overview-actions">
                <a class="ya-btn ya-btn-primary" href="mailto:<?php echo esc_attr( $sales_email ); ?>?subject=<?php echo rawurlencode( 'Request for ' . $title . ' Trip Notes' ); ?>">Request Trip Notes
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                </a>
                <a class="ya-btn ya-btn-secondary" href="<?php echo esc_url( home_url( '/bespoke-trips/' ) ); ?>">Make Itinerary Yours</a>
              </div>
            </div>

            <!-- Right Column: Top Scenic Photo + Bottom Trip Facts Card -->
            <div class="ya-overview-side ya-reveal">
              <div class="ya-side-scenic-photo">
                <img src="<?php echo esc_url( $hero_slider_images[1] ?? ( $gallery[0]['url'] ?? $hero_bg_img ) ); ?>" alt="<?php echo esc_attr( $title ); ?>" loading="lazy">
              </div>

              <div class="ya-trip-facts-card">
                <h4>TRIP FACTS</h4>
                <ul class="ya-trip-facts-list">
                  <li>
                    <span class="ya-tf-label"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg> Duration</span>
                    <strong class="ya-tf-val"><?php echo esc_html( $hero_days ); ?> Days</strong>
                  </li>
                  <li>
                    <span class="ya-tf-label"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 20L10 6l4 8 2-4 4 10H4z"/></svg> Highest Point</span>
                    <strong class="ya-tf-val"><?php echo esc_html( $fact_high_point ); ?></strong>
                  </li>
                  <li>
                    <span class="ya-tf-label"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3"/></svg> Starting Point</span>
                    <strong class="ya-tf-val"><?php echo esc_html( $fact_start ); ?></strong>
                  </li>
                  <li>
                    <span class="ya-tf-label"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3"/></svg> Ending Point</span>
                    <strong class="ya-tf-val"><?php echo esc_html( $fact_end ); ?></strong>
                  </li>
                  <li>
                    <span class="ya-tf-label"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 12h16M16 6l6 6-6 6"/></svg> Total Distance</span>
                    <strong class="ya-tf-val"><?php echo esc_html( $fact_distance ); ?></strong>
                  </li>
                  <li>
                    <span class="ya-tf-label"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2"/></svg> Best Season</span>
                    <strong class="ya-tf-val"><?php echo esc_html( $fact_season ); ?></strong>
                  </li>
                  <li>
                    <span class="ya-tf-label"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 18V8l6-4 6 4v10"/><path d="M4 20h16"/></svg> Accommodation</span>
                    <strong class="ya-tf-val"><?php echo esc_html( $fact_accom ); ?></strong>
                  </li>
                </ul>
              </div>
            </div>
          </div>

          <!-- Highlight Quote Banner -->
          <div class="ya-quote-banner ya-reveal">
            <blockquote><?php echo wp_kses_post( wp_strip_all_tags( $overview_highlight ) ); ?></blockquote>
          </div>

          <!-- Roadmap Section -->
          <div class="ya-roadmap-section ya-reveal">
            <div class="ya-roadmap-header">
              <div>
                <span class="ya-kicker">ROUTE &amp; ELEVATION</span>
                <h3>Roadmap of the <?php echo esc_html( $title ); ?> Trek</h3>
              </div>
              <div class="ya-roadmap-actions">
                <button type="button" class="ya-btn ya-btn-secondary ya-open-lightbox" data-image="<?php echo esc_url( $route_img ); ?>">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="M20 20l-4-4M11 8v6M8 11h6"/></svg>
                  VIEW FULLSCREEN MAP
                </button>
              </div>
            </div>
            <div class="ya-roadmap-frame">
              <img src="<?php echo esc_url( $route_img ); ?>" alt="<?php echo esc_attr( $title ); ?> route map" loading="lazy">
            </div>
            <div class="ya-roadmap-caption">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 21s7-5.2 7-12a7 7 0 10-14 0c0 6.8 7 12 7 12z"/><circle cx="12" cy="9" r="2.5"/></svg>
              <span>Route: <?php echo esc_html( $fact_start ); ?> → <?php echo esc_html( $fact_high_point ); ?> → <?php echo esc_html( $fact_end ); ?> · Total distance ~<?php echo esc_html( $fact_distance ); ?></span>
            </div>
          </div>

        </div>
      </section>

      <!-- Panel 2: Itinerary -->
      <section class="ya-panel" id="ya-panel-itinerary" role="tabpanel" data-panel="itinerary">
        <div class="ya-inner">
          <div class="ya-section-heading ya-reveal">
            <div class="ya-itin-head-row">
              <div>
                <span class="ya-kicker"><?php echo esc_html( $itin_kicker ); ?></span>
                <h2>Detailed Itinerary</h2>
                <p><?php echo esc_html( $itin_subtext ); ?></p>
              </div>
              <div class="ya-itin-actions">
                <button type="button" class="ya-btn-sm" id="ya-itin-expand-all">Expand All Days</button>
                <button type="button" class="ya-btn-sm" onclick="window.print()">Print Itinerary</button>
              </div>
            </div>
          </div>

          <div class="ya-itinerary-shell">
            <div class="ya-days">
              <?php foreach ( $itinerary as $i_idx => $day ) : 
                $day_num = $day['day'] ?? sprintf( '%02d', $i_idx + 1 );
                $day_stage = ! empty( $day['stage'] ) ? $day['stage'] : 'TREK';
                $day_photo = ! empty( $day['gallery'][0] ) 
                  ? $day['gallery'][0] 
                  : ( $gallery[ $i_idx % count($gallery) ]['url'] ?? $hero_bg_img );
              ?>
                <article class="ya-day <?php echo 0 === $i_idx ? 'is-open' : ''; ?>">
                  <div class="ya-day-card-grid">
                    <div class="ya-day-main">
                      <button class="ya-day-head" type="button" aria-expanded="<?php echo 0 === $i_idx ? 'true' : 'false'; ?>">
                        <div class="ya-day-title-block">
                          <span class="ya-day-tag">DAY <?php echo esc_html( $day_num ); ?> | <?php echo esc_html( strtoupper( $day_stage ) ); ?></span>
                          <strong><?php echo esc_html( $day['title'] ?? '' ); ?></strong>
                        </div>
                        <span class="ya-day-toggle"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg></span>
                      </button>
                      <div class="ya-day-body">
                        <div class="ya-day-body-inner">
                          <div class="ya-day-desc">
                            <?php echo wp_kses_post( wpautop( $day['desc'] ?? '' ) ); ?>
                          </div>
                          <?php 
                          if ( ! empty( $day['pills'] ) ) : 
                            $pills_array = array_filter( array_map( 'trim', explode( ',', $day['pills'] ) ) );
                            if ( ! empty( $pills_array ) ) :
                          ?>
                            <div class="ya-day-meta">
                              <?php foreach ( $pills_array as $pill ) : ?>
                                <span class="ya-meta-pill"><?php echo esc_html( $pill ); ?></span>
                              <?php endforeach; ?>
                            </div>
                          <?php 
                            endif;
                          endif; 
                          ?>
                        </div>
                      </div>
                    </div>
                    <div class="ya-day-photo">
                      <img src="<?php echo esc_url( $day_photo ); ?>" alt="Day <?php echo esc_attr( $day_num ); ?>: <?php echo esc_attr( $day['title'] ?? '' ); ?>" loading="lazy">
                    </div>
                  </div>
                </article>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </section>

      <!-- Panel 3: Essential Info -->
      <section class="ya-panel" id="ya-panel-essential-info" role="tabpanel" data-panel="essential-info">
        <div class="ya-inner">
          <div class="ya-section-heading ya-reveal">
            <span class="ya-kicker"><?php echo esc_html( $ess_kicker ); ?></span>
            <h2>Essential Information</h2>
            <div class="ya-ess-intro-p">
              <?php echo wp_kses_post( wpautop( $ess_intro ) ); ?>
            </div>
          </div>

          <div class="ya-essential-layout">
            <div class="ya-info-list ya-reveal">
              <?php 
              $ess_icons = array(
                '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="5" y="3" width="14" height="18" rx="2"/><path d="M9 8h6M9 12h6M9 16h4"/></svg>',
                '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>',
                '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>',
                '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>',
                '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>',
                '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 18v-6a9 9 0 0 1 18 0v6M3 14h18M3 18h18"/></svg>'
              );
              foreach ( $accordions as $a_idx => $acc ) : 
                $icon_svg = $ess_icons[ $a_idx % count($ess_icons) ];
              ?>
                <article class="ya-info-item <?php echo 0 === $a_idx ? 'is-open' : ''; ?>">
                  <button class="ya-info-head" type="button" aria-expanded="<?php echo 0 === $a_idx ? 'true' : 'false'; ?>">
                    <span>
                      <span class="ya-info-icon"><?php echo $icon_svg; ?></span>
                      <?php echo esc_html( $acc['title'] ?? '' ); ?>
                    </span>
                    <svg class="ya-info-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
                  </button>
                  <div class="ya-info-body"><div><div class="ya-info-content">
                    <?php echo wp_kses_post( $acc['content'] ?? '' ); ?>
                  </div></div></div>
                </article>
              <?php endforeach; ?>
            </div>

            <aside class="ya-trip-note-card ya-reveal">
              <div class="ya-trip-note-image" role="img" aria-label="<?php echo esc_attr( $card_title ); ?>"></div>
              <div class="ya-trip-note-copy">
                <small><?php echo esc_html( $card_kicker ); ?></small>
                <h3><?php echo esc_html( $card_title ); ?></h3>
                <p><?php echo esc_html( $card_desc ); ?></p>
                <a class="ya-btn ya-btn-light" href="mailto:<?php echo esc_attr( $card_email ); ?>?subject=<?php echo rawurlencode( 'Request for Current ' . $title . ' Trip Notes' ); ?>"><?php echo esc_html( $card_btn ); ?></a>
              </div>
            </aside>
          </div>
        </div>
      </section>

      <!-- Panel 4: Reviews -->
      <section class="ya-panel" id="ya-panel-review" role="tabpanel" data-panel="review">
        <div class="ya-inner">
          <div class="ya-section-heading ya-reveal">
            <div class="ya-rev-head-row">
              <div>
                <span class="ya-kicker"><?php echo esc_html( $rev_kicker ); ?></span>
                <h2>Traveler Reviews</h2>
                <p><?php echo esc_html( $rev_subtext ); ?></p>
              </div>
              <div>
                <a href="#ya-review-form" class="ya-btn ya-btn-dark">WRITE A REVIEW</a>
              </div>
            </div>
          </div>

          <div class="ya-review-cards ya-reveal" id="ya-review-cards-list">
            <?php foreach ( $display_reviews as $rev_idx => $rev ) : 
              $star_count = intval( $rev['rating'] ?? 5 );
              $rev_name = $rev['name'] ?? 'Traveller';
              $name_parts = explode(' ', trim($rev_name));
              $initials = (isset($name_parts[0][0]) ? $name_parts[0][0] : '') . (isset($name_parts[1][0]) ? $name_parts[1][0] : '');
              $initials = strtoupper($initials) ?: 'TR';
              $avatar_colors = array( '#1a365d', '#742a2a', '#22543d', '#744210', '#44337a', '#234e52' );
              $bg_color = $avatar_colors[ $rev_idx % count($avatar_colors) ];
            ?>
              <article class="ya-review-card">
                <div class="ya-rev-card-top">
                  <div class="ya-stars" aria-label="<?php echo esc_attr( $star_count ); ?> out of 5 stars">
                    <?php for ( $s = 0; $s < $star_count; $s++ ) : ?>
                      <svg viewBox="0 0 24 24"><path d="M12 2l3.1 6.3 6.9 1-5 4.9 1.2 6.8-6.2-3.3L5.8 21 7 14.2 2 9.3l6.9-1L12 2z"/></svg>
                    <?php endfor; ?>
                  </div>
                </div>
                <blockquote><?php echo esc_html( $rev['quote'] ?? '' ); ?></blockquote>
                <div class="ya-rev-author">
                  <span class="ya-rev-avatar" style="background-color:<?php echo esc_attr($bg_color); ?>"><?php echo esc_html($initials); ?></span>
                  <div>
                    <strong><?php echo esc_html( $rev_name ); ?></strong>
                    <small><?php echo esc_html( $rev['location'] ?? 'Verified Trekker' ); ?></small>
                  </div>
                </div>
              </article>
            <?php endforeach; ?>
          </div>

          <!-- Review Form Container -->
          <div class="ya-rev-form-wrap ya-reveal">
            <form class="ya-review-form" id="ya-review-form">
              <h3>Write a Review</h3>
              <p>Your review will be saved to our database and shared with our team.</p>
              
              <div class="ya-review-alert ya-review-alert--success" id="ya-rev-alert-success"></div>
              <div class="ya-review-alert ya-review-alert--error" id="ya-rev-alert-error"></div>

              <input type="hidden" id="ya-review-trek-id" value="<?php echo esc_attr( $post_id ); ?>">
              <input type="hidden" id="ya-review-trek-title" value="<?php echo esc_attr( $title ); ?>">

              <div class="ya-form-grid">
                <div class="ya-field"><label for="ya-review-name">Full Name *</label><input id="ya-review-name" type="text" required placeholder="e.g. John Doe"></div>
                <div class="ya-field"><label for="ya-review-email">Email *</label><input id="ya-review-email" type="email" required placeholder="e.g. john@example.com"></div>
                <div class="ya-field"><label for="ya-review-city">City</label><input id="ya-review-city" type="text" placeholder="e.g. London"></div>
                <div class="ya-field"><label for="ya-review-country">Country</label><input id="ya-review-country" type="text" placeholder="e.g. United Kingdom"></div>
                <div class="ya-field"><label for="ya-review-date">Tour Date</label><input id="ya-review-date" type="month"></div>
                <div class="ya-field"><label>Rating *</label>
                  <div class="ya-rating-row" aria-label="Rating from 1 to 5">
                    <input type="radio" id="ya-star5" name="ya-rating" value="5" checked required><label for="ya-star5" aria-label="5 stars"><svg viewBox="0 0 24 24"><path d="M12 2l3.1 6.3 6.9 1-5 4.9 1.2 6.8-6.2-3.3L5.8 21 7 14.2 2 9.3l6.9-1L12 2z"/></svg></label>
                    <input type="radio" id="ya-star4" name="ya-rating" value="4"><label for="ya-star4" aria-label="4 stars"><svg viewBox="0 0 24 24"><path d="M12 2l3.1 6.3 6.9 1-5 4.9 1.2 6.8-6.2-3.3L5.8 21 7 14.2 2 9.3l6.9-1L12 2z"/></svg></label>
                    <input type="radio" id="ya-star3" name="ya-rating" value="3"><label for="ya-star3" aria-label="3 stars"><svg viewBox="0 0 24 24"><path d="M12 2l3.1 6.3 6.9 1-5 4.9 1.2 6.8-6.2-3.3L5.8 21 7 14.2 2 9.3l6.9-1L12 2z"/></svg></label>
                    <input type="radio" id="ya-star2" name="ya-rating" value="2"><label for="ya-star2" aria-label="2 stars"><svg viewBox="0 0 24 24"><path d="M12 2l3.1 6.3 6.9 1-5 4.9 1.2 6.8-6.2-3.3L5.8 21 7 14.2 2 9.3l6.9-1L12 2z"/></svg></label>
                    <input type="radio" id="ya-star1" name="ya-rating" value="1"><label for="ya-star1" aria-label="1 star"><svg viewBox="0 0 24 24"><path d="M12 2l3.1 6.3 6.9 1-5 4.9 1.2 6.8-6.2-3.3L5.8 21 7 14.2 2 9.3l6.9-1L12 2z"/></svg></label>
                  </div>
                </div>
                <div class="ya-field ya-field-full"><label for="ya-review-comment">Comment *</label><textarea id="ya-review-comment" required placeholder="Share your experience on this trek..."></textarea></div>
                <div class="ya-field ya-field-full"><label for="ya-review-moment">Inspirational moment of your trip</label><textarea id="ya-review-moment" placeholder="What stood out most?"></textarea></div>
                <div class="ya-field ya-field-full"><label for="ya-review-leader">What did you think of your trek leader?</label><textarea id="ya-review-leader" placeholder="Feedback on guide and porters..."></textarea></div>
                <div class="ya-field ya-field-full"><label for="ya-review-advice">Advice for future travellers</label><textarea id="ya-review-advice" placeholder="Helpful tips for future trekkers..."></textarea></div>
                <div class="ya-field ya-field-full" style="display:flex; gap:12px; flex-wrap:wrap;">
                  <button class="ya-btn ya-btn-primary" type="submit" id="ya-submit-rev-btn">Submit Review to Database</button>
                  <button class="ya-btn ya-btn-secondary" type="button" id="ya-email-rev-btn">Send via Email</button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </section>

    </div>
  </section>

  <!-- Fixed Departure Dates Section -->
  <section class="ya-departures ya-reveal" id="ya-departures">
    <style>
      .ya-ebc-page .ya-departures{padding:90px 0;background:var(--cream);border-top:1px solid var(--line)}
      .ya-ebc-page .ya-dep-head{display:flex;flex-wrap:wrap;justify-content:space-between;align-items:flex-end;gap:24px;margin-bottom:48px}
      .ya-ebc-page .ya-dep-head-copy{}
      .ya-ebc-page .ya-dep-head .ya-kicker{display:block;margin-bottom:10px}
      .ya-ebc-page .ya-dep-head h2{margin:0 0 10px;font:600 clamp(28px,3vw,40px)/1.1 "Newsreader",Georgia,serif;color:var(--green)}
      .ya-ebc-page .ya-dep-head p{margin:0;color:var(--muted);font-size:15px;max-width:520px;line-height:1.7}
      .ya-ebc-page .ya-dep-table{width:100%;border-collapse:collapse;border:1px solid var(--line);border-radius:12px;overflow:hidden;box-shadow:0 4px 28px rgba(14,49,47,.06)}
      .ya-ebc-page .ya-dep-table thead tr{background:var(--green);color:#fff}
      .ya-ebc-page .ya-dep-table th{padding:14px 20px;font:600 11px/1 "Open Sans",Arial,sans-serif;text-transform:uppercase;letter-spacing:1.2px;text-align:left}
      .ya-ebc-page .ya-dep-table tbody tr{background:#fff;border-bottom:1px solid var(--line);transition:background .18s ease}
      .ya-ebc-page .ya-dep-table tbody tr:last-child{border-bottom:none}
      .ya-ebc-page .ya-dep-table tbody tr:hover{background:#f5faf7}
      .ya-ebc-page .ya-dep-table td{padding:16px 20px;font-size:14px;color:var(--ink);vertical-align:middle}
      .ya-ebc-page .ya-dep-date-cell{font:600 15px/1.3 "Newsreader",Georgia,serif}
      .ya-ebc-page .ya-dep-day{display:block;font-size:22px;color:var(--green)}
      .ya-ebc-page .ya-dep-month-year{display:block;font-size:12px;color:var(--muted);font-family:Inter,Arial,sans-serif;font-weight:500;text-transform:uppercase;letter-spacing:1px;margin-top:2px}
      .ya-ebc-page .ya-dep-price{font:600 16px/1 "Newsreader",Georgia,serif;color:var(--green)}
      .ya-ebc-page .ya-dep-price-sub{display:block;font:400 11px/1 Inter,Arial,sans-serif;color:var(--muted);margin-top:4px}
      .ya-ebc-page .ya-dep-seats{color:var(--muted);font-size:13px}
      .ya-ebc-page .ya-dep-note{color:var(--muted);font-size:12px;font-style:italic;margin-top:3px}
      .ya-ebc-page .ya-dep-status{display:inline-flex;align-items:center;gap:5px;padding:4px 11px;border-radius:20px;font:600 11px/1 Inter,Arial,sans-serif;text-transform:uppercase;letter-spacing:.7px}
      .ya-ebc-page .ya-dep-status--open{background:#edf7f0;color:#1e6b3a;border:1px solid #b7e2c7}
      .ya-ebc-page .ya-dep-status--limited{background:#fff8e6;color:#8a5c00;border:1px solid #fcd97a}
      .ya-ebc-page .ya-dep-status--soldout{background:#f5f5f5;color:#6b7280;border:1px solid #d1d5db}
      .ya-ebc-page .ya-dep-status--guaranteed{background:#f0f4ff;color:#2b4aaf;border:1px solid #c7d4fa}
      .ya-ebc-page .ya-dep-book-btn{white-space:nowrap}
      .ya-ebc-page .ya-dep-no-dates{text-align:center;padding:56px 24px;background:#fff;border:1px solid var(--line);border-radius:12px}
      .ya-ebc-page .ya-dep-no-dates svg{width:44px;height:44px;color:var(--gold);margin:0 auto 16px;display:block}
      .ya-ebc-page .ya-dep-no-dates h3{margin:0 0 10px;font:600 22px/1.2 "Newsreader",Georgia,serif;color:var(--green)}
      .ya-ebc-page .ya-dep-no-dates p{margin:0 0 22px;color:var(--muted);font-size:14px}
      @media(max-width:820px){
        .ya-ebc-page .ya-dep-table thead{display:none}
        .ya-ebc-page .ya-dep-table tbody tr{display:block;padding:16px;margin-bottom:10px;border:1px solid var(--line);border-radius:8px}
        .ya-ebc-page .ya-dep-table td{display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid var(--line)}
        .ya-ebc-page .ya-dep-table td:last-child{border-bottom:none}
        .ya-ebc-page .ya-dep-table td::before{content:attr(data-label);font:600 11px/1 Inter,Arial,sans-serif;text-transform:uppercase;letter-spacing:.8px;color:var(--muted);flex-shrink:0;margin-right:10px}
      }
    </style>
    <div class="ya-inner">
      <div class="ya-dep-head">
        <div class="ya-dep-head-copy">
          <span class="ya-kicker"><?php echo esc_html( $dep_kicker ); ?></span>
          <h2><?php echo esc_html( $dep_heading ); ?></h2>
          <p><?php echo esc_html( $dep_subtext ); ?></p>
        </div>
        <button type="button" class="ya-btn ya-btn-primary" onclick="document.querySelector('#ya-open-enquiry-modal')?.click()">
          Enquire About Dates
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:17px;height:17px;margin-left:6px"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        </button>
      </div>

      <?php if ( ! empty( $departure_dates ) ) : ?>
      <table class="ya-dep-table" aria-label="Upcoming departure dates for <?php echo esc_attr( $title ); ?>">
        <thead>
          <tr>
            <th>Departure Date</th>
            <th>Duration</th>
            <th>Price</th>
            <th>Availability</th>
            <th>Status</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ( $departure_dates as $dep ) :
            $dep_date_str = $dep['date'] ?? '';
            $dep_ts       = strtotime( $dep_date_str );
            $dep_day      = $dep_ts ? date( 'd', $dep_ts ) : '';
            $dep_mon_yr   = $dep_ts ? date( 'M Y', $dep_ts ) : $dep_date_str;
            $dep_price    = $dep['price'] ?? '';
            $dep_seats    = $dep['seats'] ?? '';
            $dep_status   = $dep['status'] ?? 'open';
            $dep_note     = $dep['note'] ?? '';

            $status_label = array(
              'open'        => 'Open',
              'limited'     => 'Limited',
              'soldout'     => 'Sold Out',
              'guaranteed'  => 'Guaranteed',
            )[ $dep_status ] ?? 'Open';
          ?>
          <tr>
            <td data-label="Date" class="ya-dep-date-cell">
              <?php if ( $dep_day ) : ?>
                <span class="ya-dep-day"><?php echo esc_html( $dep_day ); ?></span>
                <span class="ya-dep-month-year"><?php echo esc_html( $dep_mon_yr ); ?></span>
              <?php else : ?>
                <?php echo esc_html( $dep_date_str ); ?>
              <?php endif; ?>
            </td>
            <td data-label="Duration"><?php echo esc_html( $hero_days ); ?> Days</td>
            <td data-label="Price">
              <span class="ya-dep-price"><?php echo esc_html( $dep_price ?: 'Contact us' ); ?></span>
              <?php if ( $dep_price ) : ?><span class="ya-dep-price-sub">per person</span><?php endif; ?>
            </td>
            <td data-label="Availability">
              <span class="ya-dep-seats"><?php echo esc_html( $dep_seats ?: '—' ); ?></span>
              <?php if ( $dep_note ) : ?><div class="ya-dep-note"><?php echo esc_html( $dep_note ); ?></div><?php endif; ?>
            </td>
            <td data-label="Status">
              <span class="ya-dep-status ya-dep-status--<?php echo esc_attr( $dep_status ); ?>"><?php echo esc_html( $status_label ); ?></span>
            </td>
            <td data-label="Book">
              <?php if ( 'soldout' !== $dep_status ) : ?>
              <button type="button" class="ya-btn ya-btn-primary ya-dep-book-btn" onclick="document.querySelector('#ya-open-enquiry-modal')?.click()" style="padding:9px 18px;font-size:13px;">
                Book Now
              </button>
              <?php else : ?>
              <button type="button" class="ya-btn ya-dep-book-btn" disabled style="padding:9px 18px;font-size:13px;opacity:.5;cursor:not-allowed;background:#e5e7eb;color:#9ca3af;border:1px solid #d1d5db;border-radius:8px;">
                Sold Out
              </button>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <?php else : ?>
      <div class="ya-dep-no-dates">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
        <h3>Departure Dates on Request</h3>
        <p>We offer private and group departures throughout the year. Contact our team to check the current schedule and available dates that suit your plans.</p>
        <button type="button" class="ya-btn ya-btn-primary" onclick="document.querySelector('#ya-open-enquiry-modal')?.click()">
          Ask About Available Dates
        </button>
      </div>
      <?php endif; ?>
    </div>
  </section>

  <!-- Dark CTA Banner ("Ready for the Himalaya?") -->
  <section class="ya-dark-cta-section ya-reveal">
    <div class="ya-inner ya-dark-cta-inner">
      <h2 class="ya-dark-cta-heading">Ready for the Himalaya?</h2>
      <p class="ya-dark-cta-sub">Whether you want a customized itinerary, private departure, or have questions about acclimatization, our local expert team in Kathmandu is here to help.</p>
      
      <form class="ya-dark-cta-form" onsubmit="event.preventDefault(); document.querySelector('#ya-open-enquiry-modal')?.click();">
        <input type="email" placeholder="Enter your email address" required aria-label="Email address">
        <button type="submit" class="ya-btn ya-btn-gold">Start Planning</button>
      </form>

      <div class="ya-dark-cta-badges">
        <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg> Best Price Guarantee</span>
        <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg> Flexible Cancellation</span>
        <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg> 24/7 Local Support</span>
      </div>
    </div>
  </section>

  <!-- Related Journeys -->
  <section class="ya-related">
    <div class="ya-inner">
      <div class="ya-related-head ya-reveal">
        <div>
          <span class="ya-kicker">Continue Exploring</span>
          <div class="ya-script">More of <?php echo esc_html( $breadcrumb_region ); ?></div>
          <h2>Trips You Might Also Like</h2>
        </div>
        <p>Alternative journeys combining spectacular mountain scenery, culture, wildlife and remote trekking routes.</p>
      </div>

      <div class="ya-related-grid">
        <?php 
        if ( $related_query->have_posts() ) : 
          while ( $related_query->have_posts() ) : 
            $related_query->the_post();
            $r_id = get_the_ID();
            $r_days = get_post_meta( $r_id, '_trek_hero_days', true ) ?: '12';
            $r_region = get_post_meta( $r_id, '_trek_breadcrumb_region', true ) ?: 'Nepal';
            $r_thumb = get_the_post_thumbnail_url( $r_id, 'large' ) ?: get_post_meta( $r_id, '_trek_hero_bg_img', true ) ?: 'https://images.unsplash.com/photo-1544735716-392fe2489ffa?auto=format&fit=crop&w=900&q=85';
        ?>
          <a class="ya-related-card ya-reveal" href="<?php echo esc_url( get_permalink() ); ?>">
            <div class="ya-related-image"><img src="<?php echo esc_url( $r_thumb ); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy"></div>
            <div class="ya-related-copy">
              <small><?php echo esc_html( $r_days ); ?> Days · <?php echo esc_html( $r_region ); ?></small>
              <h3><?php the_title(); ?></h3>
              <div class="ya-related-link"><span>View Journey</span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg></div>
            </div>
          </a>
        <?php 
          endwhile; 
          wp_reset_postdata();
        else : 
        ?>
          <a class="ya-related-card ya-reveal" href="<?php echo esc_url( home_url( '/all-trips/' ) ); ?>">
            <div class="ya-related-image"><img src="https://images.unsplash.com/photo-1544735716-392fe2489ffa?auto=format&fit=crop&w=900&q=85" alt="Nepal landscape" loading="lazy"></div>
            <div class="ya-related-copy"><small>12 Days · Nepal</small><h3>Where Culture Meets the Wild: Nepal’s Treasures</h3><div class="ya-related-link"><span>View Journey</span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg></div></div>
          </a>
          <a class="ya-related-card ya-reveal" href="<?php echo esc_url( home_url( '/all-trips/' ) ); ?>">
            <div class="ya-related-image"><img src="https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?auto=format&fit=crop&w=900&q=85" alt="Mountain river" loading="lazy"></div>
            <div class="ya-related-copy"><small>12 Days · Nepal</small><h3>Nepal’s Wild Trio: Mountains, Rivers and Jungle</h3><div class="ya-related-link"><span>View Journey</span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg></div></div>
          </a>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <!-- Fullscreen Image Lightbox -->
  <div class="ya-lightbox" role="dialog" aria-modal="true" aria-label="Image preview">
    <button class="ya-lightbox-close" type="button" aria-label="Close image preview"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 6l12 12M18 6L6 18"/></svg></button>
    <img src="" alt="Expanded trip image">
  </div>

  <style>
    .ya-ebc-page .ya-enquiry-modal{position:fixed;inset:0;z-index:100001;display:none;place-items:center;padding:28px;background:rgba(15,25,18,.72);backdrop-filter:blur(8px)}
    .ya-ebc-page .ya-enquiry-modal.is-open{display:grid}
    .ya-ebc-page .ya-enquiry-dialog{position:relative;display:grid;grid-template-columns:minmax(330px,.9fr) minmax(460px,1.1fr);width:min(1040px,100%);max-height:calc(100svh - 56px);overflow:auto;background:#fff;border-radius:20px;box-shadow:0 32px 90px rgba(0,0,0,.35)}
    .ya-ebc-page .ya-enquiry-image{position:relative;min-height:620px;background:center / cover no-repeat;color:#fff}
    .ya-ebc-page .ya-enquiry-image:after{position:absolute;inset:0;content:"";background:linear-gradient(180deg,rgba(12,22,16,.05) 25%,rgba(12,22,16,.8) 100%)}
    .ya-ebc-page .ya-enquiry-image-copy{position:absolute;z-index:1;right:32px;bottom:30px;left:32px}
    .ya-ebc-page .ya-enquiry-image-copy small{display:block;margin-bottom:8px;font-size:13px;font-weight:600;letter-spacing:0;text-transform:none;color:#f5dd9c}
    .ya-ebc-page .ya-enquiry-image-copy strong{font:600 16px/1.35 "Newsreader",Georgia,serif}
    .ya-ebc-page .ya-enquiry-content{padding:44px 46px 40px;color:#202821}
    .ya-ebc-page .ya-enquiry-close{position:absolute;z-index:2;top:16px;right:16px;width:38px;height:38px;display:grid;place-items:center;border:1px solid #d7dfd4;border-radius:50%;background:#fff;color:#617654;cursor:pointer;transition:background .2s ease,color .2s ease,transform .2s ease}
    .ya-ebc-page .ya-enquiry-close:hover{background:#d1ad54;color:#202821;transform:rotate(90deg)}
    .ya-ebc-page .ya-enquiry-close:active{background:#617654;color:#fff;transform:rotate(90deg) scale(.92)}
    .ya-ebc-page .ya-enquiry-close svg{width:19px;height:19px}
    .ya-ebc-page .ya-enquiry-meta{display:flex;flex-wrap:wrap;gap:14px;margin-bottom:14px;color:#617654;font-size:13px;font-weight:600;letter-spacing:0;text-transform:none}
    .ya-ebc-page .ya-enquiry-meta span{display:inline-flex;align-items:center;gap:6px}
    .ya-ebc-page .ya-enquiry-meta svg{width:16px;height:16px;color:#d1ad54}
    .ya-ebc-page .ya-enquiry-content h2{margin:0;font:600 16px/1.4 "Newsreader",Georgia,serif;color:#202821;text-transform:none}
    .ya-ebc-page .ya-enquiry-intro{margin:10px 0 25px;color:#667068;font-size:14px;line-height:1.65}
    .ya-ebc-page .ya-enquiry-form{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:17px}
    .ya-ebc-page .ya-enquiry-form .ya-field{margin:0}
    .ya-ebc-page .ya-enquiry-form .ya-field-full{grid-column:1/-1}
    .ya-ebc-page .ya-enquiry-form label{display:block;margin:0 0 7px;color:#435045;font-size:14px;font-weight:600;letter-spacing:0;text-transform:none}
    .ya-ebc-page .ya-enquiry-form label em{font-weight:400}
    .ya-ebc-page .ya-enquiry-form input,.ya-ebc-page .ya-enquiry-form select,.ya-ebc-page .ya-enquiry-form textarea{width:100%;border:1px solid #d7dfd4;border-radius:10px;background:#fbfcfa;color:#202821;font-size:14px;transition:border-color .2s ease,box-shadow .2s ease,background .2s ease}
    .ya-ebc-page .ya-enquiry-form input,.ya-ebc-page .ya-enquiry-form select{height:48px;padding:0 14px}
    .ya-ebc-page .ya-enquiry-form textarea{min-height:108px;padding:13px 14px;resize:vertical}
    .ya-ebc-page .ya-enquiry-form input:focus,.ya-ebc-page .ya-enquiry-form select:focus,.ya-ebc-page .ya-enquiry-form textarea:focus{border-color:#617654;outline:0;background:#fff;box-shadow:0 0 0 3px rgba(97,118,84,.14)}
    .ya-ebc-page .ya-enquiry-form [aria-invalid="true"]{border-color:#a34231;background:#fff7f5}
    .ya-ebc-page .ya-enquiry-actions{display:block;margin-top:2px}
    .ya-ebc-page .ya-enquiry-actions .ya-btn{width:100%;min-height:50px;justify-content:center;border-radius:10px;text-transform:none}
    .ya-ebc-page .ya-enquiry-note{margin:10px 0 0;color:#667068;font-size:12px;line-height:1.5;text-align:center}
    .ya-ebc-page .ya-enquiry-alert{display:none;grid-column:1/-1;padding:12px 14px;border-radius:10px;font-size:13px;line-height:1.45}
    .ya-ebc-page .ya-enquiry-alert.is-success{display:block;background:#edf4ea;color:#315336}
    .ya-ebc-page .ya-enquiry-alert.is-error{display:block;background:#fff1ee;color:#a34231}
    .ya-ebc-page .ya-enquiry-honeypot{position:absolute!important;left:-9999px!important}
    @media(max-width:760px){.ya-ebc-page .ya-enquiry-modal{padding:14px}.ya-ebc-page .ya-enquiry-dialog{grid-template-columns:1fr;max-height:calc(100svh - 28px)}.ya-ebc-page .ya-enquiry-image{min-height:210px}.ya-ebc-page .ya-enquiry-image-copy{right:24px;bottom:22px;left:24px}.ya-ebc-page .ya-enquiry-content{padding:30px 24px}.ya-ebc-page .ya-enquiry-form{grid-template-columns:1fr}.ya-ebc-page .ya-enquiry-form .ya-field-full{grid-column:auto}}
  </style>

  <div class="ya-enquiry-modal" id="ya-enquiry-modal" role="dialog" aria-modal="true" aria-labelledby="ya-enquiry-heading" aria-hidden="true">
    <div class="ya-enquiry-dialog" role="document">
      <button class="ya-enquiry-close" type="button" aria-label="Close enquiry form"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 6l12 12M18 6L6 18"/></svg></button>
      <div class="ya-enquiry-image" style="background-image:url('<?php echo esc_url( $hero_slider_images[0] ?? $hero_bg_img ); ?>');">
        <div class="ya-enquiry-image-copy"><small><?php echo esc_html( $breadcrumb_region ); ?></small><strong><?php echo esc_html( $title ); ?></strong></div>
      </div>
      <div class="ya-enquiry-content">
        <div class="ya-enquiry-meta"><span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 21s7-5.2 7-12a7 7 0 10-14 0c0 6.8 7 12 7 12z"/><circle cx="12" cy="9" r="2.5"/></svg><?php echo esc_html( $breadcrumb_region ); ?></span><span><?php echo esc_html( $hero_days ); ?> Days</span></div>
        <h2 id="ya-enquiry-heading">Plan your <?php echo esc_html( $title ); ?> trip</h2>
        <p class="ya-enquiry-intro">Tell us a little about your travel plans and our local team will tailor this journey for you.</p>
        <form class="ya-enquiry-form" id="ya-enquiry-form" novalidate>
          <input type="hidden" id="ya-enquiry-trek-id" value="<?php echo esc_attr( $post_id ); ?>">
          <div class="ya-enquiry-honeypot" aria-hidden="true"><label>Website<input id="ya-enquiry-website" type="text" tabindex="-1" autocomplete="off"></label></div>
          <div class="ya-field"><label for="ya-enquiry-name">Full name *</label><input id="ya-enquiry-name" type="text" required minlength="2" autocomplete="name" placeholder="Your full name"></div>
          <div class="ya-field"><label for="ya-enquiry-people">Number of travellers *</label><select id="ya-enquiry-people" required><option value="" selected disabled>Select travellers</option><option>1 traveller</option><option>2 travellers</option><option>3–5 travellers</option><option>6–10 travellers</option><option>10+ travellers</option></select></div>
          <div class="ya-field"><label for="ya-enquiry-email">Email *</label><input id="ya-enquiry-email" type="email" required autocomplete="email" placeholder="you@email.com"></div>
          <div class="ya-field"><label for="ya-enquiry-phone">Phone / WhatsApp <em>(optional)</em></label><input id="ya-enquiry-phone" type="tel" autocomplete="tel" placeholder="Your phone number"></div>
          <div class="ya-field ya-field-full"><label for="ya-enquiry-message">Message</label><textarea id="ya-enquiry-message" placeholder="Tell us about your travel plans, fitness, dates or any special requests."></textarea></div>
          <div class="ya-enquiry-alert" id="ya-enquiry-alert" role="status" aria-live="polite"></div>
          <div class="ya-field ya-field-full ya-enquiry-actions">
            <button class="ya-btn ya-btn-primary" type="submit" data-enquiry-action="whatsapp">Send enquiry and continue to WhatsApp</button>
            <p class="ya-enquiry-note">Your enquiry is saved before WhatsApp opens.</p>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    (()=>{
      const root = document.querySelector('.ya-ebc-page');
      if (!root) return;

      const restUrl = '<?php echo esc_url_raw( rest_url( 'trek-manager/v1/' ) ); ?>';
      const whatsappTarget = '<?php echo esc_js( preg_replace( '/[^0-9]/', '', $whatsapp_number ) ); ?>';
      const salesEmailTarget = '<?php echo esc_js( $sales_email ); ?>';
      const tripTitle = '<?php echo esc_js( $title ); ?>';

      // Planning enquiry modal: save every enquiry to WordPress, with WhatsApp as an optional follow-up.
      const enquiryModal = root.querySelector('#ya-enquiry-modal');
      const enquiryForm = root.querySelector('#ya-enquiry-form');
      const enquiryAlert = root.querySelector('#ya-enquiry-alert');
      const closeEnquiryModal = () => {
        enquiryModal?.classList.remove('is-open');
        enquiryModal?.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
      };
      root.querySelector('#ya-open-enquiry-modal')?.addEventListener('click', () => {
        enquiryModal?.classList.add('is-open');
        enquiryModal?.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        window.setTimeout(() => root.querySelector('#ya-enquiry-name')?.focus(), 50);
      });
      enquiryModal?.querySelector('.ya-enquiry-close')?.addEventListener('click', closeEnquiryModal);
      enquiryModal?.addEventListener('click', event => { if (event.target === enquiryModal) closeEnquiryModal(); });
      document.addEventListener('keydown', event => { if (event.key === 'Escape' && enquiryModal?.classList.contains('is-open')) closeEnquiryModal(); });

      const validateEnquiryForm = () => {
        if (!enquiryForm) return false;
        const fields = [...enquiryForm.querySelectorAll('[required]')];
        let firstInvalidField;

        fields.forEach(field => {
          const valid = field.checkValidity();
          field.setAttribute('aria-invalid', String(!valid));
          if (!valid && !firstInvalidField) firstInvalidField = field;
        });

        if (!firstInvalidField) return true;
        const label = firstInvalidField.closest('.ya-field')?.querySelector('label')?.textContent.replace('*', '').trim() || 'required field';
        if (enquiryAlert) {
          enquiryAlert.textContent = `Please enter a valid ${label}.`;
          enquiryAlert.className = 'ya-enquiry-alert is-error';
        }
        firstInvalidField.focus();
        return false;
      };

      enquiryForm?.querySelectorAll('[required]').forEach(field => {
        field.addEventListener('input', () => {
          if (field.checkValidity()) field.setAttribute('aria-invalid', 'false');
        });
        field.addEventListener('change', () => {
          if (field.checkValidity()) field.setAttribute('aria-invalid', 'false');
        });
      });

      enquiryForm?.addEventListener('submit', async event => {
        event.preventDefault();
        if (!validateEnquiryForm()) return;
        const submitter = event.submitter;
        const action = submitter?.dataset.enquiryAction || 'save';
        const submitButtons = [...enquiryForm.querySelectorAll('button[type="submit"]')];
        const payload = {
          trek_id: root.querySelector('#ya-enquiry-trek-id')?.value || '',
          name: getValue('#ya-enquiry-name'),
          email: getValue('#ya-enquiry-email'),
          phone: getValue('#ya-enquiry-phone'),
          travellers: getValue('#ya-enquiry-people'),
          message: getValue('#ya-enquiry-message') || 'No additional message provided.',
          website_hp: root.querySelector('#ya-enquiry-website')?.value || '',
          source: 'single-destination-planning-popup'
        };
        if (enquiryAlert) enquiryAlert.className = 'ya-enquiry-alert';
        submitButtons.forEach(button => button.disabled = true);
        const originalText = submitter?.textContent;
        if (submitter) submitter.textContent = 'Saving your enquiry…';
        try {
          const response = await fetch(`${restUrl}enquiries`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
          });
          const data = await response.json();
          if (!response.ok || !data.success) throw new Error(data.message || 'We could not save your enquiry. Please try again.');
          if (enquiryAlert) {
            enquiryAlert.textContent = 'Thank you — your enquiry has been saved. Our team will be in touch shortly.';
            enquiryAlert.className = 'ya-enquiry-alert is-success';
          }
          enquiryForm.reset();
          if (action === 'whatsapp' && whatsappTarget) {
            const whatsappMessage = [
              `Hello Yeti Adventure, I would like to plan ${tripTitle}.`,
              `Name: ${payload.name}`,
              `Travellers: ${payload.travellers}`,
              `Email: ${payload.email}`,
              payload.phone ? `Phone / WhatsApp: ${payload.phone}` : '',
              `Message: ${payload.message}`
            ].filter(Boolean).join('\n');
            window.location.assign(`https://wa.me/${whatsappTarget}?text=${encodeURIComponent(whatsappMessage)}`);
          }
        } catch (error) {
          if (enquiryAlert) {
            enquiryAlert.textContent = error.message || 'We could not save your enquiry. Please try again.';
            enquiryAlert.className = 'ya-enquiry-alert is-error';
          }
        } finally {
          submitButtons.forEach(button => button.disabled = false);
          if (submitter && originalText) submitter.textContent = originalText;
        }
      });

      // 1. Hero background slideshow — image order comes from the destination Hero Slider Gallery.
      const heroSlides = <?php echo wp_json_encode( $hero_slider_images ); ?>;
      const heroBackgrounds = [...root.querySelectorAll('.ya-hero-background-slide')];
      const heroDots = [...root.querySelectorAll('.ya-hero-slider-dot')];
      const reduceMotion = window.matchMedia?.('(prefers-reduced-motion: reduce)').matches;
      if (heroSlides.length > 1 && heroBackgrounds.length === 2) {
        let heroSlideIndex = 0;
        let activeBackground = 0;
        let heroTimer;
        const updateHeroDots = () => {
          heroDots.forEach((dot, index) => {
            const active = index === heroSlideIndex;
            dot.classList.toggle('is-active', active);
            dot.setAttribute('aria-current', String(active));
          });
        };
        const showHeroSlide = nextIndex => {
          const targetIndex = typeof nextIndex === 'number'
            ? nextIndex
            : (heroSlideIndex + 1) % heroSlides.length;
          if (targetIndex === heroSlideIndex) return;
          const nextBackground = activeBackground === 0 ? 1 : 0;
          const outgoingBackground = heroBackgrounds[activeBackground];
          const incomingBackground = heroBackgrounds[nextBackground];
          heroSlideIndex = targetIndex;
          incomingBackground.classList.remove('is-active', 'is-exiting');
          incomingBackground.style.backgroundImage = `url(${JSON.stringify(heroSlides[heroSlideIndex])})`;
          void incomingBackground.offsetWidth;
          incomingBackground.classList.add('is-active');
          outgoingBackground.classList.remove('is-active');
          outgoingBackground.classList.add('is-exiting');
          window.setTimeout(() => outgoingBackground.classList.remove('is-exiting'), 1250);
          activeBackground = nextBackground;
          updateHeroDots();
        };
        const startHeroTimer = () => {
          window.clearInterval(heroTimer);
          if (!reduceMotion) heroTimer = window.setInterval(showHeroSlide, 4000);
        };
        heroDots.forEach(dot => dot.addEventListener('click', () => {
          showHeroSlide(Number(dot.dataset.heroSlide));
          startHeroTimer();
        }));
        startHeroTimer();
        document.addEventListener('visibilitychange', () => {
          window.clearInterval(heroTimer);
          if (!document.hidden) startHeroTimer();
        });
      }

      // 2. Reveal on scroll animations
      const revealItems = root.querySelectorAll('.ya-reveal');
      if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver(entries => {
          entries.forEach(entry => {
            if (entry.isIntersecting) {
              entry.target.classList.add('is-visible');
              observer.unobserve(entry.target);
            }
          });
        }, { threshold: 0.1 });
        revealItems.forEach((item, index) => {
          item.style.transitionDelay = `${Math.min(index * 35, 210)}ms`;
          observer.observe(item);
        });
      } else {
        revealItems.forEach(item => item.classList.add('is-visible'));
      }

      // 2. Tab navigation
      const tabs = [...root.querySelectorAll('.ya-tab')];
      const panels = [...root.querySelectorAll('.ya-panel')];
      const tabsList = root.querySelector('.ya-tabs');
      const tabStage = root.querySelector('.ya-tab-stage');
      const mobileTabs = window.matchMedia('(max-width: 1100px)');

      // On mobile, place each panel directly below its matching navigation row.
      // Desktop restores every panel to the main reading column.
      const syncMobileTabLayout = () => {
        if (!tabsList || !tabStage) return;

        if (mobileTabs.matches) {
          tabsList.removeAttribute('role');
          panels.forEach(panel => {
            const tab = tabs.find(item => item.dataset.tab === panel.dataset.panel);
            if (tab && panel.previousElementSibling !== tab) tab.insertAdjacentElement('afterend', panel);
          });
        } else {
          tabsList.setAttribute('role', 'tablist');
          panels.forEach(panel => tabStage.appendChild(panel));
        }
      };

      syncMobileTabLayout();
      if (mobileTabs.addEventListener) {
        mobileTabs.addEventListener('change', syncMobileTabLayout);
      } else {
        mobileTabs.addListener(syncMobileTabLayout);
      }

      const setActiveTab = name => {
        const targetTab = tabs.find(tab => tab.dataset.tab === name);
        const targetPanel = panels.find(panel => panel.dataset.panel === name);
        if (!targetTab || !targetPanel) return;

        tabs.forEach(tab => {
          const active = tab === targetTab;
          tab.classList.toggle('is-active', active);
          tab.setAttribute('aria-selected', String(active));
        });
        panels.forEach(panel => panel.classList.toggle('is-active', panel === targetPanel));
        return { targetTab, targetPanel };
      };

      const activateTab = (name, scroll = true) => {
        const active = setActiveTab(name);
        if (!active) return;
        const { targetTab, targetPanel } = active;
        if (!mobileTabs.matches) {
          targetTab.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
        }

        try { history.replaceState(null, '', `#${name}`); } catch (error) {}
        if (scroll && !mobileTabs.matches) {
          targetPanel.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
      };

      tabs.forEach(tab => tab.addEventListener('click', () => {
        const tabName = tab.dataset.tab;
        const scrollTarget = tab.dataset.scrollTarget;
        activateTab(tabName, true);
        if (scrollTarget) {
          setTimeout(() => {
            const targetEl = root.querySelector(scrollTarget);
            if (targetEl) {
              targetEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
          }, 150);
        }
      }));
      root.querySelectorAll('.ya-jump-tab').forEach(button => button.addEventListener('click', () => activateTab(button.dataset.target, true)));

      const initialHash = window.location.hash.replace('#', '');
      if (tabs.some(tab => tab.dataset.tab === initialHash)) activateTab(initialHash, false);

      // Keep the visual navigation state aligned with the visible long-form section.
      if ('IntersectionObserver' in window) {
        const sectionObserver = new IntersectionObserver(entries => {
          const visible = entries
            .filter(entry => entry.isIntersecting)
            .sort((a, b) => a.boundingClientRect.top - b.boundingClientRect.top)[0];
          if (visible?.target?.dataset?.panel) {
            setActiveTab(visible.target.dataset.panel);
          }
        }, { rootMargin: '-18% 0px -65% 0px', threshold: 0.01 });
        panels.forEach(panel => sectionObserver.observe(panel));
      }

      // Reading progress for the long-form content, shown directly below the fixed left navigation.
      const readingProgress = root.querySelector('.ya-reading-progress-bar');
      if (readingProgress && panels.length) {
        let progressFrame;
        const updateReadingProgress = () => {
          progressFrame = undefined;
          const firstPanelTop = panels[0].getBoundingClientRect().top + window.scrollY;
          const lastPanelBottom = panels[panels.length - 1].getBoundingClientRect().bottom + window.scrollY;
          const viewportMarker = window.scrollY + window.innerHeight * .45;
          const distance = Math.max(1, lastPanelBottom - firstPanelTop);
          const progress = Math.min(100, Math.max(0, ((viewportMarker - firstPanelTop) / distance) * 100));
          readingProgress.style.setProperty('--ya-reading-progress', `${progress}%`);
        };
        const requestProgressUpdate = () => {
          if (!progressFrame) progressFrame = window.requestAnimationFrame(updateReadingProgress);
        };
        window.addEventListener('scroll', requestProgressUpdate, { passive: true });
        window.addEventListener('resize', requestProgressUpdate);
        requestProgressUpdate();
      }

      // 3. Day-by-Day Accordion
      root.querySelectorAll('.ya-day-head').forEach(button => {
        button.addEventListener('click', () => {
          const day = button.closest('.ya-day');
          const open = day.classList.toggle('is-open');
          button.setAttribute('aria-expanded', String(open));
        });
      });

      // 4. Essential Info Accordion
      root.querySelectorAll('.ya-info-head').forEach(button => {
        button.addEventListener('click', () => {
          const item = button.closest('.ya-info-item');
          const open = item.classList.toggle('is-open');
          button.setAttribute('aria-expanded', String(open));
        });
      });

      // 5. Image Lightbox
      const lightbox = root.querySelector('.ya-lightbox');
      const lightboxImage = lightbox?.querySelector('img');
      const closeLightbox = () => {
        lightbox?.classList.remove('is-open');
        document.body.style.overflow = '';
      };
      root.querySelectorAll('.ya-open-lightbox').forEach(button => {
        button.addEventListener('click', () => {
          if (!lightbox || !lightboxImage) return;
          lightboxImage.src = button.dataset.image || '';
          lightbox.classList.add('is-open');
          document.body.style.overflow = 'hidden';
        });
      });
      lightbox?.querySelector('.ya-lightbox-close')?.addEventListener('click', closeLightbox);
      lightbox?.addEventListener('click', event => { if (event.target === lightbox) closeLightbox(); });
      document.addEventListener('keydown', event => { if (event.key === 'Escape') closeLightbox(); });

      const getValue = selector => root.querySelector(selector)?.value?.trim() || 'Not provided';

      // 6. Review Submission (Save to Database via REST API & AJAX)
      const reviewForm = root.querySelector('#ya-review-form');
      const alertSuccess = root.querySelector('#ya-rev-alert-success');
      const alertError = root.querySelector('#ya-rev-alert-error');

      const buildReviewEmail = () => {
        const rating = root.querySelector('input[name="ya-rating"]:checked')?.value || '5';
        const body = [
          `${tripTitle} Trip Review`,
          '',
          `Name: ${getValue('#ya-review-name')}`,
          `Email: ${getValue('#ya-review-email')}`,
          `City: ${getValue('#ya-review-city')}`,
          `Country: ${getValue('#ya-review-country')}`,
          `Tour date: ${getValue('#ya-review-date')}`,
          `Rating: ${rating}/5`,
          '',
          `Comment: ${getValue('#ya-review-comment')}`,
          '',
          `Inspirational moment: ${getValue('#ya-review-moment')}`,
          '',
          `Trek leader: ${getValue('#ya-review-leader')}`,
          '',
          `Advice for travellers: ${getValue('#ya-review-advice')}`
        ].join('\n');
        window.location.href = `mailto:${salesEmailTarget}?subject=${encodeURIComponent(tripTitle + ' Trip Review')}&body=${encodeURIComponent(body)}`;
      };

      root.querySelector('#ya-email-rev-btn')?.addEventListener('click', () => {
        if (!reviewForm.reportValidity()) return;
        buildReviewEmail();
      });

      reviewForm?.addEventListener('submit', async event => {
        event.preventDefault();
        if (!reviewForm.reportValidity()) return;

        const submitBtn = root.querySelector('#ya-submit-rev-btn');
        if (submitBtn) {
          submitBtn.disabled = true;
          submitBtn.textContent = 'Saving Review...';
        }

        if (alertSuccess) alertSuccess.style.display = 'none';
        if (alertError) alertError.style.display = 'none';

        const payload = {
          trek_id: getValue('#ya-review-trek-id'),
          name: getValue('#ya-review-name'),
          email: getValue('#ya-review-email'),
          city: getValue('#ya-review-city'),
          country: getValue('#ya-review-country'),
          tour_date: getValue('#ya-review-date'),
          rating: root.querySelector('input[name="ya-rating"]:checked')?.value || '5',
          comment: getValue('#ya-review-comment'),
          moment: getValue('#ya-review-moment'),
          leader: getValue('#ya-review-leader'),
          advice: getValue('#ya-review-advice'),
        };

        try {
          const response = await fetch(`${restUrl}reviews`, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
            },
            body: JSON.stringify(payload)
          });

          const data = await response.json();

          if (response.ok && data.success) {
            if (alertSuccess) {
              alertSuccess.innerHTML = `<strong>✔ Review Submitted!</strong> Thank you ${payload.name}, your review has been saved in our database. <a href="${data.whatsapp_url}" target="_blank" style="text-decoration:underline; font-weight:bold; color:inherit; margin-left:6px;">Share on WhatsApp &rarr;</a>`;
              alertSuccess.style.display = 'block';
            }

            // Dynamically prepend review card to the list
            const reviewList = root.querySelector('#ya-review-cards-list');
            if (reviewList) {
              const stars = '<svg viewBox="0 0 24 24"><path d="M12 2l3.1 6.3 6.9 1-5 4.9 1.2 6.8-6.2-3.3L5.8 21 7 14.2 2 9.3l6.9-1L12 2z"/></svg>'.repeat(parseInt(payload.rating) || 5);
              const card = document.createElement('article');
              card.className = 'ya-review-card';
              card.innerHTML = `
                <div class="ya-stars" aria-label="${payload.rating} out of 5 stars">${stars}</div>
                <blockquote>${payload.comment}</blockquote>
                <strong>${payload.name}</strong>
                <small>${payload.city ? payload.city + ', ' : ''}${payload.country || 'Verified Traveller'}</small>
              `;
              reviewList.insertBefore(card, reviewList.firstChild);
            }

            reviewForm.reset();
          } else {
            throw new Error(data.message || 'Failed to submit review.');
          }
        } catch (err) {
          if (alertError) {
            alertError.textContent = err.message || 'An error occurred while saving your review. You can also send it via email.';
            alertError.style.display = 'block';
          }
        } finally {
          if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Submit Review to Database';
          }
        }
      });
    })();
  </script>
</section>