/**
 * Trek Content Manager - Master Dynamic Rendering & Interaction Engine
 * 
 * Modular Architecture:
 * - TrekRenderer: Dynamic HTML card & section generation from canonical data
 * - TrekNav: Sticky navigation with scrollspy, keyboard support & hash sync
 * - TrekItinerary: Accordion day builder & activity type dynamic rendering
 * - TrekLightbox: Accessible modal gallery with keyboard traps & swipe
 * - TrekForms: Public booking enquiry, departure modals & review submissions
 * - TrekDataQualityUI: Real-time consistency audits & completeness alerts
 * - TrekRoleManager: Live role switching, mode transitions & permission gating
 */

(function(window, document) {
  'use strict';

  // Preserve the WordPress context localized by PHP instead of overwriting it.
  window.PAGE_CONTEXT = Object.assign({
    userRole: "admin",
    mode: "view",
    activeTrekId: "trek-nepal-langtang-001"
  }, window.PAGE_CONTEXT || {});

  const TrekApp = {
    currentData: null,
    scrubbedData: null,
    lightboxIndex: 0,
    lightboxImages: [],

    /**
     * Initialize the Application
     */
    init: function() {
      if (!window.PAGE_CONTEXT) {
        window.PAGE_CONTEXT = {};
      }

      const defaultId = window.PAGE_CONTEXT.activeTrekId || 'langtang-kyanjin-trek';
      const sampleDatasets = window.TREK_SAMPLE_DATASETS || {};
      this.currentData = sampleDatasets[defaultId] || window.TREK_DATA || {};

      // 2. Initial Render
      this.renderPage();

      // 3. Bind Global Event Handlers
      this.bindEvents();
    },

    /**
     * Render the entire detail page dynamically
     */
    renderPage: function() {
      const role = window.PAGE_CONTEXT.userRole;
      const mode = window.PAGE_CONTEXT.mode;

      // Cleanse data based on role (purges internal notes in public view)
      this.scrubbedData = window.TrekRoles.scrubDataForRole(this.currentData, role, mode);
      const data = this.scrubbedData;

      if (!data) return;

      // 1. Render Role Toolbar
      this.renderRoleToolbar();

      // 2. Render Data Quality Panel (Authorized roles only)
      this.renderDataQualityPanel();

      // 3. Render Hero Section
      this.renderHero(data);

      // 4. Render Quick Facts Grid
      this.renderQuickFacts(data);

      // 5. Render Dynamic Sticky Navigation
      this.renderNavigation(data);

      // 6. Render Overview & Route Information
      this.renderOverview(data);

      // 7. Render Highlights Cards
      this.renderHighlights(data);

      // 8. Render Expandable Itinerary
      this.renderItinerary(data);

      // 9. Render Accommodations
      this.renderAccommodation(data);

      // 10. Render Included / Excluded Services
      this.renderIncludedExcluded(data);

      // 11. Render Safety, Permits & Insurance
      this.renderSafetyPermits(data);

      // 12. Render Dynamic Packing List
      this.renderPackingList(data);

      // 13. Render Essential Information Accordion
      this.renderEssentialInfo(data);

      // 14. Render Documents & Downloads
      this.renderDocuments(data);

      // 15. Render Gallery
      this.renderGallery(data);

      // 16. Render Fixed Departures
      this.renderDepartures(data);

      // 18. Render Reviews
      this.renderReviews(data);

      // 19. Render Enquiry Form Context
      this.renderEnquiryForm(data);

      // 20. Render Related Trips
      this.renderRelatedTrips(data);

      // 21. Re-initialize interactive components
      this.initScrollSpy();
    },

    // =========================================================================
    // SECTION RENDERERS
    // =========================================================================

    /**
     * 1. Top Role & Mode Management Toolbar
     */
    renderRoleToolbar: function() {
      const el = document.getElementById('trek-role-toolbar-container');
      if (!el) return;

      const currentRole = window.PAGE_CONTEXT.userRole;
      const currentMode = window.PAGE_CONTEXT.mode;
      const audit = window.TrekValidation.auditTrek(this.currentData);

      el.innerHTML = `
        <div class="trek-role-toolbar" role="region" aria-label="Role and Content Management Bar">
          <div class="trek-role-toolbar__inner">
            <div class="trek-role-toolbar__left">
              <div class="trek-role-toolbar__brand">
                <span>🏔️</span> <strong>Trek Manager</strong>
              </div>
              
              <div class="trek-role-toolbar__select-group">
                <label for="trek-role-select">Role:</label>
                <select id="trek-role-select" aria-label="Select User Role">
                  <option value="admin" ${currentRole === 'admin' ? 'selected' : ''}>Administrator</option>
                  <option value="manager" ${currentRole === 'manager' ? 'selected' : ''}>Content Manager</option>
                  <option value="staff" ${currentRole === 'staff' ? 'selected' : ''}>Staff Member</option>
                  <option value="editor" ${currentRole === 'editor' ? 'selected' : ''}>Editor</option>
                  <option value="reviewer" ${currentRole === 'reviewer' ? 'selected' : ''}>Reviewer / Approver</option>
                  <option value="public" ${currentRole === 'public' ? 'selected' : ''}>Public Traveller</option>
                </select>
              </div>

              <div class="trek-role-toolbar__select-group">
                <label for="trek-dest-select">Destination:</label>
                <select id="trek-dest-select" aria-label="Select Destination Sample">
                  <option value="langtang-kyanjin-trek" ${this.currentData.slug === 'langtang-kyanjin-trek' ? 'selected' : ''}>Langtang & Kyanjin (10 Days)</option>
                  <option value="annapurna-sanctuary-trek" ${this.currentData.slug === 'annapurna-sanctuary-trek' ? 'selected' : ''}>Annapurna Sanctuary (12 Days)</option>
                </select>
              </div>

              <span class="trek-role-toolbar__badge trek-role-toolbar__badge--published">
                ● ${this.currentData.status || 'Published'}
              </span>
            </div>

            <div class="trek-role-toolbar__right">
              ${currentRole !== 'public' ? `
                <div class="trek-role-toolbar__score" title="Data Completeness Score">
                  <span>Completeness:</span>
                  <div class="trek-score-circle">${audit.score}%</div>
                </div>

                <button type="button" class="trek-btn-admin trek-btn-admin--outline" id="trek-toggle-quality-btn">
                  <span>📋</span> Audit (${audit.issues.length})
                </button>

                ${window.TrekRoles.hasPermission(currentRole, 'editContent') ? `
                  <button type="button" class="trek-btn-admin" id="trek-quick-edit-btn">
                    <span>✏️</span> Edit Data
                  </button>
                ` : ''}

                ${window.TrekRoles.hasPermission(currentRole, 'viewRevisions') ? `
                  <button type="button" class="trek-btn-admin trek-btn-admin--outline" id="trek-view-diff-btn">
                    <span>🕒</span> Revisions
                  </button>
                ` : ''}

                ${window.TrekRoles.hasPermission(currentRole, 'publish') ? `
                  <button type="button" class="trek-btn-admin" style="background-color:#10b981; color:#fff;" id="trek-publish-btn">
                    <span>🚀</span> Publish
                  </button>
                ` : ''}
              ` : `
                <span style="font-size:0.75rem; color:#a1b5ae;">Public Visitor Mode (Private notes & admin controls stripped)</span>
              `}
            </div>
          </div>
        </div>
      `;
    },

    /**
     * 2. Data Quality & Completeness Panel
     */
    renderDataQualityPanel: function() {
      const container = document.getElementById('trek-data-quality-container');
      if (!container) return;

      const role = window.PAGE_CONTEXT.userRole;
      if (role === 'public') {
        container.innerHTML = '';
        return;
      }

      const audit = window.TrekValidation.auditTrek(this.currentData);

      container.innerHTML = `
        <div class="trek-container" style="margin-top:20px;">
          <div class="trek-data-quality-panel" id="trek-quality-panel-box" style="display:none;">
            <div class="trek-data-quality-panel__header">
              <h2 class="trek-data-quality-panel__title">
                <span>🛡️</span> Data Quality & Consistency Audit (${audit.score}% Complete)
              </h2>
              <button type="button" class="trek-btn-text" style="color:#fcd34d;" onclick="document.getElementById('trek-quality-panel-box').style.display='none'">✕ Close</button>
            </div>
            <div class="trek-data-quality-panel__body">
              ${audit.issues.length === 0 ? `
                <div class="trek-quality-alert trek-quality-alert--success">
                  <span>✓</span> All consistency rules and required data checks passed successfully.
                </div>
              ` : audit.issues.map(issue => `
                <div class="trek-quality-alert trek-quality-alert--${issue.severity}">
                  <span>${issue.severity === 'error' ? '🚫' : issue.severity === 'warning' ? '⚠️' : 'ℹ️'}</span>
                  <div><strong>${issue.severity.toUpperCase()}:</strong> ${issue.message}</div>
                </div>
              `).join('')}
            </div>
          </div>
        </div>
      `;
    },

    /**
     * 3. Hero Section
     */
    renderHero: function(data) {
      const hero = data.hero || {};
      const geo = data.geography || {};
      const facts = data.keyFacts || {};

      document.getElementById('trek-hero-bg').src = hero.image || '';
      document.getElementById('trek-hero-bg').alt = hero.imageAlt || data.title;

      document.getElementById('trek-breadcrumb-dest').textContent = geo.country || 'Nepal';
      document.getElementById('trek-breadcrumb-region').textContent = geo.region || '';
      document.getElementById('trek-hero-kicker').textContent = hero.eyebrow || 'Himalayan Expedition';
      document.getElementById('trek-hero-title').textContent = data.title || '';
      document.getElementById('trek-hero-subtitle').textContent = hero.subtitle || '';
      document.getElementById('trek-hero-duration').textContent = `⏱️ ${facts.duration || 0} Days`;
      document.getElementById('trek-hero-difficulty').textContent = `🏔️ ${facts.difficulty || 'Moderate'}`;

      const primaryCTA = document.getElementById('trek-hero-primary-cta');
      if (primaryCTA) {
        primaryCTA.href = hero.primaryCTA?.url || '#departures';
        primaryCTA.textContent = hero.primaryCTA?.label || 'View Departures';
      }
    },

    /**
     * 4. Floating Quick Facts Grid
     */
    renderQuickFacts: function(data) {
      const container = document.getElementById('trek-facts-grid-container');
      if (!container) return;

      const f = data.keyFacts || {};
      const geo = data.geography || {};

      // Dynamic array of quick facts
      const factList = [
        { label: 'Duration', value: f.duration ? `${f.duration} Days` : null, icon: '⏱️' },
        { label: 'Max Altitude', value: f.highestPoint ? `${f.highestPoint.toLocaleString()}m` : null, icon: '⛰️' },
        { label: 'Difficulty', value: f.difficulty, icon: '🥾' },
        { label: 'Region', value: geo.region, icon: '📍' },
        { label: 'Best Season', value: f.bestSeason, icon: '🌤️' },
        { label: 'Starting Price', value: data.pricing?.price ? `$${data.pricing.price} ${data.pricing.currency || 'USD'}` : null, icon: '🏷️' }
      ].filter(item => item.value);

      container.innerHTML = factList.map(fact => `
        <div class="trek-fact-card">
          <div class="trek-fact-card__icon" aria-hidden="true">${fact.icon}</div>
          <div>
            <div class="trek-fact-card__label">${fact.label}</div>
            <div class="trek-fact-card__value">${fact.value}</div>
          </div>
        </div>
      `).join('');
    },

    /**
     * 5. Dynamic Sticky Navigation Tabs
     */
    renderNavigation: function(data) {
      const container = document.getElementById('trek-nav-tabs-container');
      if (!container) return;

      const tabs = [
        { id: 'overview', label: 'Overview', condition: !!data.overview },
        { id: 'highlights', label: 'Highlights', condition: Array.isArray(data.highlights) && data.highlights.length > 0 },
        { id: 'itinerary', label: 'Itinerary', condition: Array.isArray(data.itinerary) && data.itinerary.length > 0 },
        { id: 'accommodation', label: 'Accommodation', condition: Array.isArray(data.accommodation) && data.accommodation.length > 0 },
        { id: 'included', label: 'Cost & Inclusions', condition: !!data.included },
        { id: 'safety', label: 'Safety & Permits', condition: !!data.safety },
        { id: 'packing', label: 'Packing List', condition: Array.isArray(data.packingList) && data.packingList.length > 0 },
        { id: 'essential-info', label: 'Essential Info', condition: Array.isArray(data.essentialInfo) && data.essentialInfo.length > 0 },
        { id: 'gallery', label: 'Gallery', condition: Array.isArray(data.gallery) && data.gallery.length > 0 },
        { id: 'departures', label: 'Departures & Booking', condition: true },
        { id: 'reviews', label: 'Reviews', condition: Array.isArray(data.reviews) && data.reviews.length > 0 }
      ];

      container.innerHTML = tabs
        .filter(t => t.condition)
        .map((t, idx) => `
          <a href="#${t.id}" class="trek-nav-tab ${idx === 0 ? 'is-active' : ''}" data-target="${t.id}">
            ${t.label}
          </a>
        `).join('');
    },

    /**
     * 6. Overview & Route Data
     */
    renderOverview: function(data) {
      const ov = data.overview || {};
      const geo = data.geography || {};
      const f = data.keyFacts || {};

      const introEl = document.getElementById('trek-overview-intro');
      if (introEl) introEl.innerHTML = `<p>${ov.intro || ''}</p>`;

      const quoteEl = document.getElementById('trek-overview-quote');
      if (quoteEl) quoteEl.textContent = ov.highlightQuote || '';

      const routeDescEl = document.getElementById('trek-overview-route-desc');
      if (routeDescEl) routeDescEl.innerHTML = `<p>${ov.routeDescription || ''}</p>`;

      // Route Meta Summary Card
      const routeList = document.getElementById('trek-route-meta-list');
      if (routeList) {
        routeList.innerHTML = `
          <div class="trek-route-meta-item">
            <span class="trek-route-meta-item__label">Trailhead</span>
            <span class="trek-route-meta-item__val">${geo.trailhead || geo.startingPoint || 'N/A'}</span>
          </div>
          <div class="trek-route-meta-item">
            <span class="trek-route-meta-item__label">Route Type</span>
            <span class="trek-route-meta-item__val">${geo.routeType || 'Out and Back'}</span>
          </div>
          <div class="trek-route-meta-item">
            <span class="trek-route-meta-item__label">Walking Distance</span>
            <span class="trek-route-meta-item__val">${geo.distance ? `${geo.distance} ${geo.distanceUnit || 'km'}` : 'N/A'}</span>
          </div>
          <div class="trek-route-meta-item">
            <span class="trek-route-meta-item__label">National Park</span>
            <span class="trek-route-meta-item__val">${geo.nationalPark || 'N/A'}</span>
          </div>
          <div class="trek-route-meta-item">
            <span class="trek-route-meta-item__label">Gateway City</span>
            <span class="trek-route-meta-item__val">${geo.gatewayCity || 'Kathmandu'}</span>
          </div>
        `;
      }
    },

    /**
     * 7. Highlights Cards Grid
     */
    renderHighlights: function(data) {
      const container = document.getElementById('trek-highlights-container');
      if (!container) return;

      const items = data.highlights || [];
      container.innerHTML = items.map((hl, idx) => `
        <article class="trek-highlight-card">
          <div class="trek-highlight-card__img-wrap">
            <img src="${hl.image}" alt="${hl.imageAlt || hl.title}" class="trek-highlight-card__img" loading="lazy">
            <div class="trek-highlight-card__num">${hl.number || (idx + 1)}</div>
          </div>
          <div class="trek-highlight-card__body">
            <h3 class="trek-highlight-card__title">${hl.title}</h3>
            <p class="trek-highlight-card__desc">${hl.description}</p>
          </div>
        </article>
      `).join('');
    },

    /**
     * 8. Expandable Day-by-Day Itinerary
     */
    renderItinerary: function(data) {
      const container = document.getElementById('trek-itinerary-container');
      if (!container) return;

      const days = data.itinerary || [];
      container.innerHTML = days.map((day, idx) => {
        const isFirst = idx === 0;
        const activities = day.activityTypes || ['Trek'];

        return `
          <div class="trek-itinerary-day ${isFirst ? 'is-open' : ''}" data-day="${day.dayNumber}">
            <div class="trek-itinerary-day__summary" role="button" tabindex="0" aria-expanded="${isFirst}">
              <div class="trek-itinerary-day__left">
                <div class="trek-day-badge">
                  <span class="trek-day-badge__label">Day</span>
                  <span class="trek-day-badge__num">${day.dayNumber}</span>
                </div>
                <div class="trek-itinerary-day__info">
                  <h3 class="trek-itinerary-day__title">${day.title}</h3>
                  <div class="trek-itinerary-day__route-tags">
                    ${activities.map(act => `
                      <span class="trek-activity-tag trek-activity-tag--${act.toLowerCase()}">${act}</span>
                    `).join('')}
                    ${day.walkingHours ? `<span style="font-size:0.75rem; color:#5c6863;">• ~${day.walkingHours} hrs walk</span>` : ''}
                    ${day.highestAltitude ? `<span style="font-size:0.75rem; color:#5c6863;">• Peak ${day.highestAltitude.toLocaleString()}m</span>` : ''}
                  </div>
                </div>
              </div>
              <div class="trek-itinerary-day__toggle-icon" aria-hidden="true">▼</div>
            </div>

            <div class="trek-itinerary-day__content">
              <div class="trek-day-stats-grid">
                <div class="trek-day-stat-item">
                  <span class="trek-day-stat-item__label">Start Altitude</span>
                  <span class="trek-day-stat-item__val">${day.elevationStart ? `${day.elevationStart.toLocaleString()}m` : '—'}</span>
                </div>
                <div class="trek-day-stat-item">
                  <span class="trek-day-stat-item__label">End Altitude</span>
                  <span class="trek-day-stat-item__val">${day.elevationEnd ? `${day.elevationEnd.toLocaleString()}m` : '—'}</span>
                </div>
                <div class="trek-day-stat-item">
                  <span class="trek-day-stat-item__label">Max Elevation</span>
                  <span class="trek-day-stat-item__val" style="color:#d4af37;">${day.highestAltitude ? `${day.highestAltitude.toLocaleString()}m` : '—'}</span>
                </div>
                <div class="trek-day-stat-item">
                  <span class="trek-day-stat-item__label">Distance</span>
                  <span class="trek-day-stat-item__val">${day.walkingDistance ? `${day.walkingDistance} km` : (day.drivingDistance ? `${day.drivingDistance} km drive` : '—')}</span>
                </div>
              </div>

              <div class="trek-day-details-layout">
                <div class="trek-day-desc">
                  <p>${day.description}</p>
                </div>
                <div class="trek-day-sidebar">
                  ${day.accommodationName ? `
                    <div class="trek-day-box">
                      <div class="trek-day-box__title">🏨 Accommodation</div>
                      <div>${day.accommodationName}</div>
                    </div>
                  ` : ''}
                  ${day.mealPlan ? `
                    <div class="trek-day-box">
                      <div class="trek-day-box__title">🍲 Meal Basis</div>
                      <div>${day.mealPlan}</div>
                    </div>
                  ` : ''}
                  ${day.safetyNote ? `
                    <div class="trek-day-box trek-day-box--safety">
                      <div class="trek-day-box__title">⚠️ Safety Advisory</div>
                      <div>${day.safetyNote}</div>
                    </div>
                  ` : ''}
                </div>
              </div>
            </div>
          </div>
        `;
      }).join('');
    },

    /**
     * 9. Accommodation Cards
     */
    renderAccommodation: function(data) {
      const container = document.getElementById('trek-accommodation-container');
      if (!container) return;

      const accs = data.accommodation || [];
      container.innerHTML = accs.map(acc => `
        <div class="trek-acc-card">
          <img src="${acc.image || 'https://images.unsplash.com/photo-1517824806704-9040b037703b?auto=format&fit=crop&w=600&q=80'}" alt="${acc.propertyName}" class="trek-acc-card__img" loading="lazy">
          <div class="trek-acc-card__body">
            <div class="trek-acc-card__type">${acc.propertyType} • ${acc.nights} Nights</div>
            <h3 class="trek-acc-card__name">${acc.propertyName}</h3>
            <div class="trek-acc-card__details">
              <span>📍 ${acc.location}</span>
              <span>🛏️ ${acc.roomType}</span>
              <span>🚿 ${acc.bathroomType}</span>
            </div>
            <p class="trek-acc-card__desc">${acc.description}</p>
          </div>
        </div>
      `).join('');
    },

    /**
     * 10. Included vs Excluded Services
     */
    renderIncludedExcluded: function(data) {
      const incContainer = document.getElementById('trek-included-list');
      const excContainer = document.getElementById('trek-excluded-list');

      if (incContainer && data.included) {
        incContainer.innerHTML = data.included.map(inc => `
          <li class="trek-inex-item">
            <div class="trek-inex-item__icon">✓</div>
            <div class="trek-inex-item__text">
              <strong>${inc.title}</strong>
              <span>${inc.description}</span>
            </div>
          </li>
        `).join('');
      }

      if (excContainer && data.excluded) {
        excContainer.innerHTML = data.excluded.map(exc => `
          <li class="trek-inex-item">
            <div class="trek-inex-item__icon">✕</div>
            <div class="trek-inex-item__text">
              <strong>${exc.title}</strong>
              <span>${exc.description}</span>
            </div>
          </li>
        `).join('');
      }
    },

    /**
     * 11. Safety, Permits & Emergency Protocols
     */
    renderSafetyPermits: function(data) {
      const s = data.safety || {};
      const p = data.permitsAndVisa || {};

      const safetyBody = document.getElementById('trek-safety-body');
      if (safetyBody) {
        safetyBody.innerHTML = `
          <p><strong>Fitness Level:</strong> ${s.healthLevel || 'Good cardiovascular stamina'}</p>
          <p><strong>Altitude Advisory:</strong> ${s.altitudeRisk || 'Gradual acclimatization profile'}</p>
          <div class="trek-insurance-alert">
            <strong>Mandatory Insurance:</strong> ${s.insuranceInformation || 'High altitude medical evacuation coverage required.'}
          </div>
          <div style="margin-top:14px; font-size:0.875rem;">
            <div><strong>Emergency Coordinator:</strong> ${s.emergencyContact || '+977 98510 00000'}</div>
            <div><strong>Rescue Logistics:</strong> ${s.rescueProvider || 'Helicopter evacuation partner on standby'}</div>
          </div>
        `;
      }

      const permitsBody = document.getElementById('trek-permits-body');
      if (permitsBody && p.permits) {
        permitsBody.innerHTML = `
          <p style="margin-bottom:12px;">${p.visaInformation || 'Nepal Visa on arrival available at Tribhuvan Airport.'}</p>
          ${p.permits.map(permit => `
            <div class="trek-permit-pill">
              <div>
                <strong>${permit.name}</strong>
                <div style="font-size:0.75rem; color:#5c6863;">${permit.authority}</div>
              </div>
              <span class="trek-badge-pill" style="font-size:0.6875rem;">Included</span>
            </div>
          `).join('')}
        `;
      }
    },

    /**
     * 12. Dynamic Packing List
     */
    renderPackingList: function(data) {
      const container = document.getElementById('trek-packing-container');
      if (!container) return;

      const items = data.packingList || [];
      container.innerHTML = items.map(item => `
        <div class="trek-packing-item" data-category="${item.category}">
          <div>
            <div class="trek-packing-item__name">${item.item}</div>
            <div style="font-size:0.75rem; color:#5c6863;">${item.category} ${item.quantity ? `• ${item.quantity}` : ''}</div>
          </div>
          <span class="trek-packing-item__badge trek-packing-item__badge--${(item.requiredLevel || 'required').toLowerCase()}">
            ${item.requiredLevel || 'Required'}
          </span>
        </div>
      `).join('');
    },

    /**
     * 13. Essential Information Accordions
     */
    renderEssentialInfo: function(data) {
      const container = document.getElementById('trek-essential-info-container');
      if (!container) return;

      const sections = data.essentialInfo || [];
      container.innerHTML = sections.map((sec, idx) => `
        <div class="trek-accordion-item ${idx === 0 ? 'is-open' : ''}">
          <button type="button" class="trek-accordion-btn" aria-expanded="${idx === 0}">
            <span>${sec.title}</span>
            <span class="trek-accordion-icon" aria-hidden="true">▼</span>
          </button>
          <div class="trek-accordion-content">
            <p>${sec.content}</p>
          </div>
        </div>
      `).join('');
    },

    /**
     * 14. Documents & Downloads
     */
    renderDocuments: function(data) {
      const container = document.getElementById('trek-documents-container');
      if (!container) return;

      const docs = data.documents || [];
      if (docs.length === 0) {
        container.innerHTML = '<p style="color:#5c6863;">No documents currently attached.</p>';
        return;
      }

      container.innerHTML = docs.map(doc => `
        <div class="trek-fact-card" style="border:1px solid var(--trek-color-border); border-radius:8px;">
          <div class="trek-fact-card__icon">📄</div>
          <div style="flex-grow:1;">
            <div style="font-weight:700; color:var(--trek-color-primary);">${doc.title}</div>
            <div style="font-size:0.8125rem; color:#5c6863;">${doc.description || ''} (${doc.fileType} • ${doc.fileSize || ''})</div>
          </div>
          <a href="${doc.fileUrl}" class="trek-btn-admin trek-btn-admin--outline" style="padding:6px 12px;" download>Download</a>
        </div>
      `).join('');
    },

    /**
     * 15. Gallery & Lightbox Setup
     */
    renderGallery: function(data) {
      const container = document.getElementById('trek-gallery-container');
      if (!container) return;

      const images = data.gallery || [];
      this.lightboxImages = images;

      container.innerHTML = images.map((img, idx) => `
        <div class="trek-gallery-item" data-index="${idx}" role="button" tabindex="0" aria-label="View photo: ${img.caption || img.alt}">
          <img src="${img.thumbnail || img.url}" alt="${img.alt || 'Himalayan mountain view'}" loading="lazy">
          <div class="trek-gallery-item__caption">${img.caption || img.alt}</div>
        </div>
      `).join('');
    },

    /**
     * 16. Fixed Departures Table
     */
    renderDepartures: function(data) {
      const container = document.getElementById('trek-departures-container');
      if (!container) return;

      const deps = data.departures || [];
      if (deps.length === 0) {
        container.innerHTML = '<p>No fixed group departures currently scheduled. Please enquire for private custom dates.</p>';
        return;
      }

      container.innerHTML = `
        <table class="trek-departures-table">
          <thead>
            <tr>
              <th>Departure Dates</th>
              <th>Duration</th>
              <th>Price per Person</th>
              <th>Availability</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            ${deps.map(dep => `
              <tr class="trek-departure-row">
                <td>${dep.startDate} – ${dep.endDate}</td>
                <td>${dep.duration}</td>
                <td><strong>$${dep.price}</strong> <span style="font-size:0.75rem; color:#5c6863;">${dep.currency || 'USD'}</span></td>
                <td>
                  <span class="trek-status-badge trek-status-badge--${dep.status.toLowerCase()}">
                    ● ${dep.status} (${dep.remainingSpaces} spots left)
                  </span>
                </td>
                <td>
                  <button type="button" class="trek-btn-primary" style="padding:8px 18px; font-size:0.8125rem;" onclick="TrekApp.openDepartureBooking('${dep.id}', '${dep.startDate}')">
                    Book Space
                  </button>
                </td>
              </tr>
            `).join('')}
          </tbody>
        </table>
      `;
    },

    /**
     * 18. Reviews & Moderation
     */
    renderReviews: function(data) {
      const container = document.getElementById('trek-reviews-container');
      if (!container) return;

      const revs = data.reviews || [];
      container.innerHTML = revs.map(rev => `
        <div class="trek-review-card">
          <div class="trek-review-card__stars">★★★★★</div>
          <h3 class="trek-review-card__title">“${rev.title}”</h3>
          <p class="trek-review-card__text">${rev.review}</p>
          <div class="trek-review-card__author">
            <div class="trek-review-avatar">${rev.name.charAt(0)}</div>
            <div>
              <div class="trek-review-author__name">${rev.name}</div>
              <div class="trek-review-author__loc">${rev.city ? `${rev.city}, ` : ''}${rev.country} • ${rev.tourDate || ''}</div>
            </div>
          </div>
        </div>
      `).join('');
    },

    /**
     * 19. Public Enquiry Form Context
     */
    renderEnquiryForm: function(data) {
      const titleSpan = document.getElementById('trek-enquiry-trek-title');
      if (titleSpan) titleSpan.textContent = data.title;
    },

    /**
     * 20. Related Trips
     */
    renderRelatedTrips: function(data) {
      const container = document.getElementById('trek-related-container');
      if (!container) return;

      const trips = data.relatedTrips || [];
      container.innerHTML = trips.map(trip => `
        <div class="trek-highlight-card">
          <div class="trek-highlight-card__img-wrap">
            <img src="${trip.image}" alt="${trip.title}" class="trek-highlight-card__img" loading="lazy">
          </div>
          <div class="trek-highlight-card__body">
            <div style="font-size:0.75rem; text-transform:uppercase; color:#d4af37; font-weight:700;">${trip.country} • ${trip.duration}</div>
            <h3 class="trek-highlight-card__title">${trip.title}</h3>
            <div style="font-weight:700; color:var(--trek-color-primary); margin-top:8px;">From $${trip.price} ${trip.currency || 'USD'}</div>
          </div>
        </div>
      `).join('');
    },

    // =========================================================================
    // EVENT BINDINGS & INTERACTION HANDLERS
    // =========================================================================

    bindEvents: function() {
      const self = this;

      // 1. Role Change Handler
      document.addEventListener('change', function(e) {
        if (e.target && e.target.id === 'trek-role-select') {
          window.PAGE_CONTEXT.userRole = e.target.value;
          self.renderPage();
          self.showToast(`Switched view to ${e.target.options[e.target.selectedIndex].text}`);
        }

        if (e.target && e.target.id === 'trek-dest-select') {
          const destKey = e.target.value;
          const sampleDatasets = window.TREK_SAMPLE_DATASETS || {};
          self.currentData = sampleDatasets[destKey] || sampleDatasets['langtang-kyanjin-trek'] || window.TREK_DATA;
          if (!self.currentData) return;
          self.renderPage();
          self.showToast(`Loaded ${self.currentData.title}`);
        }
      });

      // 2. Toolbar Buttons
      document.addEventListener('click', function(e) {
        // Toggle Quality Panel
        if (e.target.closest('#trek-toggle-quality-btn')) {
          const panel = document.getElementById('trek-quality-panel-box');
          if (panel) {
            panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
          }
        }

        // Quick Edit Modal
        if (e.target.closest('#trek-quick-edit-btn')) {
          self.openEditModal();
        }

        // Diff Viewer Modal
        if (e.target.closest('#trek-view-diff-btn')) {
          self.openDiffModal();
        }

        // Publish Action
        if (e.target.closest('#trek-publish-btn')) {
          self.showToast('✓ Trek destination published successfully to WordPress REST API!');
        }

        // Itinerary Day Accordion Toggle
        const dayHeader = e.target.closest('.trek-itinerary-day__summary');
        if (dayHeader) {
          const dayCard = dayHeader.closest('.trek-itinerary-day');
          const isOpen = dayCard.classList.contains('is-open');
          dayCard.classList.toggle('is-open', !isOpen);
          dayHeader.setAttribute('aria-expanded', !isOpen);
        }

        // Expand All / Collapse All Itinerary Days
        if (e.target.id === 'trek-expand-all-itinerary') {
          document.querySelectorAll('.trek-itinerary-day').forEach(d => {
            d.classList.add('is-open');
            d.querySelector('.trek-itinerary-day__summary')?.setAttribute('aria-expanded', 'true');
          });
        }
        if (e.target.id === 'trek-collapse-all-itinerary') {
          document.querySelectorAll('.trek-itinerary-day').forEach(d => {
            d.classList.remove('is-open');
            d.querySelector('.trek-itinerary-day__summary')?.setAttribute('aria-expanded', 'false');
          });
        }

        // Essential Info Accordion
        const accBtn = e.target.closest('.trek-accordion-btn');
        if (accBtn) {
          const accItem = accBtn.closest('.trek-accordion-item');
          const isOpen = accItem.classList.contains('is-open');
          accItem.classList.toggle('is-open', !isOpen);
          accBtn.setAttribute('aria-expanded', !isOpen);
        }

        // Gallery Item Lightbox Trigger
        const galleryItem = e.target.closest('.trek-gallery-item');
        if (galleryItem) {
          const idx = parseInt(galleryItem.getAttribute('data-index'), 10);
          self.openLightbox(idx);
        }

        // Packing List Tab Filter
        const packingBtn = e.target.closest('.trek-packing-tab-btn');
        if (packingBtn) {
          document.querySelectorAll('.trek-packing-tab-btn').forEach(b => b.classList.remove('is-active'));
          packingBtn.classList.add('is-active');
          const category = packingBtn.getAttribute('data-category');
          document.querySelectorAll('.trek-packing-item').forEach(item => {
            if (category === 'all' || item.getAttribute('data-category') === category) {
              item.style.display = 'flex';
            } else {
              item.style.display = 'none';
            }
          });
        }
      });

      // 3. Enquiry Date Type (Fixed vs Flexible Toggle)
      document.addEventListener('change', function(e) {
        if (e.target.name === 'datePreference') {
          const isFixed = e.target.value === 'fixed';
          const fixedGroup = document.getElementById('trek-fixed-dates-group');
          const flexGroup = document.getElementById('trek-flexible-dates-group');
          if (fixedGroup && flexGroup) {
            fixedGroup.style.display = isFixed ? 'grid' : 'none';
            flexGroup.style.display = isFixed ? 'none' : 'grid';
          }
        }
      });

      // 4. Form Submissions
      const enquiryForm = document.getElementById('trek-public-enquiry-form');
      if (enquiryForm) {
        enquiryForm.addEventListener('submit', function(e) {
          e.preventDefault();

          // Check Honeypot spam field
          const honeypot = document.getElementById('trek-form-hp');
          if (honeypot && honeypot.value !== '') {
            console.warn('Spam submission detected.');
            return;
          }

          self.showToast('✓ Enquiry received! Our travel team will contact you within 24 hours.');
          enquiryForm.reset();
        });
      }

      // 5. Lightbox Keyboard Support
      document.addEventListener('keydown', function(e) {
        const lightbox = document.getElementById('trek-lightbox-modal');
        if (lightbox && lightbox.classList.contains('is-active')) {
          if (e.key === 'Escape') self.closeLightbox();
          if (e.key === 'ArrowRight') self.nextLightbox();
          if (e.key === 'ArrowLeft') self.prevLightbox();
        }
      });
    },

    // =========================================================================
    // LIGHTBOX COMPONENT
    // =========================================================================
    openLightbox: function(index) {
      this.lightboxIndex = index;
      const lb = document.getElementById('trek-lightbox-modal');
      if (!lb || !this.lightboxImages[index]) return;

      const img = this.lightboxImages[index];
      document.getElementById('trek-lightbox-img').src = img.url;
      document.getElementById('trek-lightbox-caption').textContent = img.caption || img.alt || '';

      lb.classList.add('is-active');
      document.body.style.overflow = 'hidden';
    },

    closeLightbox: function() {
      const lb = document.getElementById('trek-lightbox-modal');
      if (lb) {
        lb.classList.remove('is-active');
        document.body.style.overflow = '';
      }
    },

    nextLightbox: function() {
      this.lightboxIndex = (this.lightboxIndex + 1) % this.lightboxImages.length;
      this.openLightbox(this.lightboxIndex);
    },

    prevLightbox: function() {
      this.lightboxIndex = (this.lightboxIndex - 1 + this.lightboxImages.length) % this.lightboxImages.length;
      this.openLightbox(this.lightboxIndex);
    },

    // =========================================================================
    // MODALS & LIVE EDIT SIMULATOR
    // =========================================================================
    openDepartureBooking: function(depId, dateStr) {
      const modal = document.getElementById('trek-booking-modal');
      if (!modal) return;
      document.getElementById('trek-booking-dep-info').textContent = `Booking Departure: ${dateStr}`;
      modal.classList.add('is-active');
    },

    closeModal: function(modalId) {
      const m = document.getElementById(modalId);
      if (m) m.classList.remove('is-active');
    },

    openEditModal: function() {
      const modal = document.getElementById('trek-edit-modal');
      if (!modal) return;

      document.getElementById('edit-title-input').value = this.currentData.title || '';
      document.getElementById('edit-duration-input').value = this.currentData.keyFacts?.duration || 10;
      document.getElementById('edit-price-input').value = this.currentData.pricing?.price || 1190;
      document.getElementById('edit-highest-input').value = this.currentData.keyFacts?.highestPoint || 3870;

      modal.classList.add('is-active');
    },

    saveLiveEdit: function() {
      const newTitle = document.getElementById('edit-title-input').value;
      const newDuration = parseInt(document.getElementById('edit-duration-input').value, 10);
      const newPrice = parseInt(document.getElementById('edit-price-input').value, 10);
      const newHighest = parseInt(document.getElementById('edit-highest-input').value, 10);

      this.currentData.title = newTitle;
      this.currentData.hero.title = newTitle;
      this.currentData.keyFacts.duration = newDuration;
      this.currentData.pricing.price = newPrice;
      this.currentData.keyFacts.highestPoint = newHighest;

      this.closeModal('trek-edit-modal');
      this.renderPage();
      this.showToast('✓ Changes applied to live data model!');
    },

    openDiffModal: function() {
      const modal = document.getElementById('trek-diff-modal');
      if (modal) modal.classList.add('is-active');
    },

    // =========================================================================
    // SCROLLSPY & TOAST
    // =========================================================================
    initScrollSpy: function() {
      const tabs = document.querySelectorAll('.trek-nav-tab');
      const sections = Array.from(tabs).map(t => document.getElementById(t.getAttribute('data-target'))).filter(Boolean);

      window.addEventListener('scroll', function() {
        let currentSectionId = '';
        const scrollPos = window.scrollY + 140;

        sections.forEach(sec => {
          if (sec.offsetTop <= scrollPos) {
            currentSectionId = sec.id;
          }
        });

        tabs.forEach(tab => {
          if (tab.getAttribute('data-target') === currentSectionId) {
            tab.classList.add('is-active');
          } else {
            tab.classList.remove('is-active');
          }
        });
      }, { passive: true });
    },

    showToast: function(msg) {
      let container = document.getElementById('trek-toast-box');
      if (!container) {
        container = document.createElement('div');
        container.id = 'trek-toast-box';
        container.className = 'trek-toast-container';
        document.body.appendChild(container);
      }

      const toast = document.createElement('div');
      toast.className = 'trek-toast';
      toast.textContent = msg;
      container.appendChild(toast);

      setTimeout(() => {
        toast.remove();
      }, 4000);
    }
  };

  // Expose engine to window
  window.TrekApp = TrekApp;

  // Initialize once DOM is loaded
  document.addEventListener('DOMContentLoaded', function() {
    TrekApp.init();
  });

})(window, document);
