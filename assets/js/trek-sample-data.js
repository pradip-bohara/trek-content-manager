/**
 * Trek Content Manager - Master Sample Datasets
 * 
 * Primary Dataset: Langtang & Kyanjin Trek (10 Days, Nepal)
 * Secondary Datasets: Annapurna Sanctuary, Everest Panorama
 * 
 * Demonstrates 100% data-driven rendering across all 65+ master content fields.
 */

(function(window) {
  'use strict';

  const langtangData = {
    // 1. Core Identity
    id: "trek-nepal-langtang-001",
    title: "Langtang & Kyanjin Trek",
    shortTitle: "Langtang Valley Trek",
    slug: "langtang-kyanjin-trek",
    status: "published",
    featured: true,
    category: "Himalayan Valley Trek",
    tripType: "Lodge & Tea House Trek",

    // 2. Geography
    geography: {
      continent: "Asia",
      country: "Nepal",
      region: "Langtang Himal",
      subRegion: "Rasuwa District",
      nationalPark: "Langtang National Park",
      gatewayCity: "Kathmandu",
      startingPoint: "Syabrubesi",
      endingPoint: "Syabrubesi",
      trailhead: "Syabrubesi (1,460m)",
      routeType: "Out and Back",
      routeDirection: "North-East into Langtang Valley",
      distance: 78,
      distanceUnit: "km",
      latitude: 28.2117,
      longitude: 85.5684,
      mapUrl: "https://images.unsplash.com/photo-1544735716-392fe2489ffa?auto=format&fit=crop&w=1200&q=80"
    },

    // 3. Hero
    hero: {
      eyebrow: "Sacred Valley of Glaciers",
      title: "Langtang & Kyanjin Trek",
      subtitle: "A breathtaking alpine journey through dense bamboo forests, Tamang heritage settlements, and the glacial amphitheater of Kyanjin Gompa beneath towering 7,000m peaks.",
      image: "https://images.unsplash.com/photo-1544735716-392fe2489ffa?auto=format&fit=crop&w=1920&q=85",
      imageAlt: "Snow-capped peaks of Langtang Lirung rising above the alpine valley",
      video: null,
      primaryCTA: { label: "Book Departure", url: "#departures" },
      secondaryCTA: { label: "View Itinerary", url: "#itinerary" }
    },

    // 4. Key Facts (Dynamic Repeatable Facts Array)
    keyFacts: {
      duration: 10,
      nights: 9,
      country: "Nepal",
      region: "Langtang Himal",
      highestPoint: 3870,
      highestSleepingAltitude: 3870,
      lowestAltitude: 1400,
      trekkingDays: 7,
      difficulty: "Moderate",
      tripStyle: "Tea House & Mountain Lodge",
      groupSize: "2 - 12 Travellers",
      bestSeason: "Mar–May & Sep–Nov",
      distance: 78
    },

    // 5. Overview & Route Character
    overview: {
      intro: "The Langtang Valley, known culturally as 'the Valley of Glaciers', offers one of the most accessible yet deeply rewarding Himalayan treks in Nepal. Beginning with a scenic overland drive from Kathmandu to the bustling trailhead at Syabrubesi, the trail follows the cascading Langtang Khola through subtropical rhododendron, pine, and oak forests before breaking out into wide glacial pastureland.",
      highlightQuote: "“To stand before the ancient stone monastery of Kyanjin Gompa with Langtang Lirung looming overhead is to experience the raw, spiritual grandeur of the High Himalaya.”",
      routeDescription: "Traversing Langtang National Park, this trail ascends gradually along the river gorge, passing through traditional Tamang villages that have rebuilt with remarkable resilience following the 2015 earthquake. The climax is reached at Kyanjin Gompa (3,870m), where hikers can summit Kyanjin Ri (4,773m) or Tserko Ri (4,984m) for panoramic vistas of Langtang Lirung (7,227m), Dorje Lakpa, and Gangchempo.",
      destinationCharacter: "Culturally rich Tibetan Buddhist heritage, hospitable Tamang lodges, artisan yak cheese dairies, and pristine glacier moraines."
    },

    // 6. Highlights Card Grid
    highlights: [
      {
        id: "hl-1",
        number: 1,
        title: "Kyanjin Gompa (3,870m)",
        description: "The spiritual heart of the valley featuring an ancient Buddhist monastery, artisan yak cheese factory, and 360-degree mountain amphitheater.",
        image: "https://images.unsplash.com/photo-1544735716-392fe2489ffa?auto=format&fit=crop&w=600&q=80",
        imageAlt: "Monastery flags fluttering in Kyanjin Gompa"
      },
      {
        id: "hl-2",
        number: 2,
        title: "Kyanjin Ri & Tserko Ri Viewpoints",
        description: "Optional viewpoint climbs offering panoramic vistas of Langtang Lirung (7,227m), Kinshung, Yansa Tsenji, and the Tibetan border peaks.",
        image: "https://images.unsplash.com/photo-1486870591958-9b9d0d1dda99?auto=format&fit=crop&w=600&q=80",
        imageAlt: "Majestic snow ridge overlooking the valley"
      },
      {
        id: "hl-3",
        number: 3,
        title: "Langtang National Park Flora & Fauna",
        description: "Encounter dense rhododendron blooms, moss-draped pines, and potential sightings of red pandas, Himalayan monals, and musk deer.",
        image: "https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=600&q=80",
        imageAlt: "Alpine river rushing through deep pine valley"
      },
      {
        id: "hl-4",
        number: 4,
        title: "Tamang Heritage & Mountain Hospitality",
        description: "Experience the unique cultural warmth, prayer wheels, stone-carved mani walls, and authentic teahouse hospitality of the Tamang people.",
        image: "https://images.unsplash.com/photo-1517824806704-9040b037703b?auto=format&fit=crop&w=600&q=80",
        imageAlt: "Traditional Himalayan village nestled in the valley"
      }
    ],

    // 7. Day-by-Day Itinerary
    itinerary: [
      {
        dayNumber: 1,
        title: "Arrival in Kathmandu & Trip Briefing",
        startLocation: "Tribhuvan International Airport (1,400m)",
        endLocation: "Kathmandu Hotel",
        activityTypes: ["Arrival", "Explore"],
        walkingHours: 0,
        walkingDistance: 0,
        elevationStart: 1400,
        elevationEnd: 1400,
        highestAltitude: 1400,
        accommodationName: "Hotel Mulberry / Dwarika's (Standard/Premium)",
        mealPlan: "Welcome Dinner",
        description: "Upon arrival at Kathmandu Airport, meet our private airport representative and transfer to your hotel in Thamel. In the afternoon, attend the comprehensive trek briefing, check equipment with your lead guide, and enjoy a traditional Nepali welcome dinner.",
        notes: "Airport transfer included. Passport details collected for national park permit processing."
      },
      {
        dayNumber: 2,
        title: "Scenic Overland Drive from Kathmandu to Syabrubesi",
        startLocation: "Kathmandu (1,400m)",
        endLocation: "Syabrubesi (1,460m)",
        activityTypes: ["Drive"],
        drivingHours: 7,
        drivingDistance: 132,
        elevationStart: 1400,
        elevationEnd: 1460,
        highestAltitude: 1980,
        accommodationName: "Local Mountain Lodge, Syabrubesi",
        mealPlan: "Breakfast, Lunch, Dinner",
        description: "Embark on an adventurous private 4WD overland drive heading north out of the Kathmandu valley. The road climbs over the ridges of Trisuli Bazaar and Dunche, entering the gateway of Langtang National Park. Settle into the trailhead village of Syabrubesi along the riverbank.",
        notes: "Scenic mountain road with stops for tea and lunch."
      },
      {
        dayNumber: 3,
        title: "Trek from Syabrubesi to Lama Hotel via Bamboo",
        startLocation: "Syabrubesi (1,460m)",
        endLocation: "Lama Hotel (2,470m)",
        activityTypes: ["Trek"],
        walkingHours: 6,
        walkingDistance: 11.5,
        elevationStart: 1460,
        elevationEnd: 2470,
        highestAltitude: 2470,
        elevationGain: 1010,
        accommodationName: "Riverside Lodge, Lama Hotel",
        mealPlan: "Breakfast, Lunch, Dinner",
        description: "Cross the suspension bridge over the Bhote Koshi and follow the Langtang Khola river gorge. The trail ascends steadily through dense subtropical forests of oak, maple, and bamboo, crossing small wooden bridges with waterfalls cascading from high cliffs.",
        safetyNote: "Gradual uphill trail; maintain steady hydration and pace."
      },
      {
        dayNumber: 4,
        title: "Trek from Lama Hotel to Langtang Village via Ghodatabela",
        startLocation: "Lama Hotel (2,470m)",
        endLocation: "New Langtang Village (3,430m)",
        activityTypes: ["Trek"],
        walkingHours: 5.5,
        walkingDistance: 14.2,
        elevationStart: 2470,
        elevationEnd: 3430,
        highestAltitude: 3430,
        elevationGain: 960,
        accommodationName: "Peaceful Lodge / Eco Resort, Langtang Village",
        mealPlan: "Breakfast, Lunch, Dinner",
        description: "Continue ascending through pine forests to the army checkpoint at Ghodatabela ('Horse Stable'). Here the valley broadens dramatically into alpine meadows. Pass mani stones and prayer wheels into the resilient rebuilt settlement of New Langtang Village beneath Langtang Lirung.",
        notes: "National Park permit stamped at Ghodatabela checkpoint."
      },
      {
        dayNumber: 5,
        title: "Ascend to Kyanjin Gompa & Visit Yak Cheese Factory",
        startLocation: "Langtang Village (3,430m)",
        endLocation: "Kyanjin Gompa (3,870m)",
        activityTypes: ["Trek", "Acclimatization"],
        walkingHours: 3.5,
        walkingDistance: 6.8,
        elevationStart: 3430,
        elevationEnd: 3870,
        highestAltitude: 3870,
        elevationGain: 440,
        accommodationName: "Kyanjin Valley Guesthouse",
        mealPlan: "Breakfast, Lunch, Dinner",
        description: "A short, scenic morning trek past stone watermills, yak pastures, and the largest mani wall in the valley. Arrive at Kyanjin Gompa by midday. Spend the afternoon exploring the historic monastery, sampling freshly churned yak cheese, and acclimating to the 3,870m elevation.",
        notes: "Relaxed afternoon to support physiological altitude adaptation."
      },
      {
        dayNumber: 6,
        title: "Acclimatization Day: Summit Kyanjin Ri or Tserko Ri",
        startLocation: "Kyanjin Gompa (3,870m)",
        endLocation: "Kyanjin Gompa (3,870m)",
        activityTypes: ["Trek", "Acclimatization", "Sightseeing"],
        walkingHours: 5,
        walkingDistance: 7.5,
        elevationStart: 3870,
        elevationEnd: 3870,
        highestAltitude: 4773,
        elevationGain: 903,
        accommodationName: "Kyanjin Valley Guesthouse",
        mealPlan: "Breakfast, Lunch, Dinner",
        description: "An unforgettable day of high-altitude exploration. Take an early morning ascent up Kyanjin Ri (4,773m) or the more demanding Tserko Ri (4,984m) for an awe-inspiring panorama of ice flutes, glacier seracs, and peaks including Langtang Lirung, Yala Peak, and Dorje Lakpa.",
        safetyNote: "Early start recommended for clear summit visibility before midday clouds."
      },
      {
        dayNumber: 7,
        title: "Descent from Kyanjin Gompa to Lama Hotel",
        startLocation: "Kyanjin Gompa (3,870m)",
        endLocation: "Lama Hotel (2,470m)",
        activityTypes: ["Trek"],
        walkingHours: 6,
        walkingDistance: 21,
        elevationStart: 3870,
        elevationEnd: 2470,
        highestAltitude: 3870,
        accommodationName: "Riverside Lodge, Lama Hotel",
        mealPlan: "Breakfast, Lunch, Dinner",
        description: "Retrace your steps down the wide valley, relishing the downhill pace with rich oxygen returning to your lungs. Pass through Mundu, Langtang Village, and Ghodatabela, finishing the day back in the lush rhododendron groves of Lama Hotel.",
        notes: "Long descent; trekking poles recommended for knee support."
      },
      {
        dayNumber: 8,
        title: "Trek from Lama Hotel to Syabrubesi via Bamboo Trail",
        startLocation: "Lama Hotel (2,470m)",
        endLocation: "Syabrubesi (1,460m)",
        activityTypes: ["Trek"],
        walkingHours: 5,
        walkingDistance: 11.5,
        elevationStart: 2470,
        elevationEnd: 1460,
        highestAltitude: 2470,
        accommodationName: "Local Mountain Lodge, Syabrubesi",
        mealPlan: "Breakfast, Lunch, Dinner",
        description: "Complete your final day on the trail descending along the cascading river. Cross the suspension bridges back into Syabrubesi. Celebrate the successful trek completion with your mountain guide and porter team in the evening.",
        notes: "Gratuities & farewell gathering with local crew."
      },
      {
        dayNumber: 9,
        title: "Return Scenic Drive from Syabrubesi to Kathmandu",
        startLocation: "Syabrubesi (1,460m)",
        endLocation: "Kathmandu (1,400m)",
        activityTypes: ["Drive"],
        drivingHours: 7,
        drivingDistance: 132,
        elevationStart: 1460,
        elevationEnd: 1400,
        highestAltitude: 1980,
        accommodationName: "Hotel Mulberry / Dwarika's, Kathmandu",
        mealPlan: "Breakfast, Farewell Dinner",
        description: "Board your private vehicle for the return journey back to Kathmandu. Check in to your hotel, refresh, and enjoy free time for shopping in Thamel before joining our celebratory farewell dinner.",
        notes: "Evening free for leisure and souvenir exploration."
      },
      {
        dayNumber: 10,
        title: "Final Departure from Kathmandu",
        startLocation: "Kathmandu Hotel",
        endLocation: "Tribhuvan International Airport (1,400m)",
        activityTypes: ["Departure"],
        walkingHours: 0,
        elevationStart: 1400,
        elevationEnd: 1400,
        highestAltitude: 1400,
        accommodationName: null,
        mealPlan: "Breakfast",
        description: "Enjoy breakfast at your hotel. Our private driver will meet you 3 hours prior to your scheduled flight departure for transfer to Tribhuvan International Airport for your flight home.",
        notes: "Complimentary airport drop-off."
      }
    ],

    // 8. Accommodation
    accommodation: [
      {
        propertyName: "Heritage & Boutique Hotels, Kathmandu",
        propertyType: "Hotel",
        location: "Kathmandu",
        nights: 2,
        roomType: "Twin / Double Ensuite",
        mealBasis: "Bed & Breakfast",
        bathroomType: "Private Ensuite with Hot Shower",
        description: "Comfortable boutique hotel in central Kathmandu featuring modern amenities, complimentary Wi-Fi, air conditioning, and peaceful courtyard gardens.",
        image: "https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=600&q=80"
      },
      {
        propertyName: "Authentic Mountain Teahouses & Lodges",
        propertyType: "Tea House / Mountain Lodge",
        location: "Syabrubesi, Lama Hotel, Langtang Village, Kyanjin Gompa",
        nights: 7,
        roomType: "Twin Share Rooms",
        mealBasis: "Full Board (Breakfast, Lunch, Dinner)",
        bathroomType: "Shared / Selected Private Bathrooms",
        description: "Family-run Himalayan teahouses with heated dining rooms, freshly prepared hearty mountain meals, charging points, and warm hospitality.",
        image: "https://images.unsplash.com/photo-1517824806704-9040b037703b?auto=format&fit=crop&w=600&q=80"
      }
    ],

    // 9. Pricing
    pricing: {
      priceType: "from",
      price: 1190,
      currency: "USD",
      adultPrice: 1190,
      childPrice: 950,
      singleSupplement: 180,
      minimumTravellers: 2,
      maximumTravellers: 12,
      deposit: 250,
      balanceDue: "30 days prior to arrival",
      priceNotes: "Guaranteed departures with minimum 2 travellers. Private bespoke dates available upon request."
    },

    // 10. Included Services
    included: [
      {
        title: "Airport Transfers",
        description: "Private airport arrival and departure transfers in Kathmandu.",
        category: "Transportation",
        icon: "🚗"
      },
      {
        title: "All Overland Transport",
        description: "Private 4WD vehicle for Kathmandu to Syabrubesi round trip.",
        category: "Transportation",
        icon: "🚙"
      },
      {
        title: "All Accommodations",
        description: "2 nights in Kathmandu hotel and 7 nights in authentic mountain teahouses.",
        category: "Accommodation",
        icon: "🏨"
      },
      {
        title: "Full Board Meals on Trek",
        description: "Breakfast, lunch, and dinner daily on the trekking route plus welcome and farewell dinners.",
        category: "Meals",
        icon: "🍲"
      },
      {
        title: "Licensed English-Speaking Guide",
        description: "Government-certified high-altitude guide trained in wilderness first aid and altitude management.",
        category: "Guide",
        icon: "🧗"
      },
      {
        title: "Porter Support & Duffel Bag",
        description: "Porters to carry baggage (1 porter for every 2 trekkers, max 15kg per trekker) plus complimentary company duffel bag.",
        category: "Porter",
        icon: "🎒"
      },
      {
        title: "National Park & TIMS Permits",
        description: "Langtang National Park entry permit and Trekkers' Information Management System (TIMS) cards.",
        category: "Permits",
        icon: "📄"
      },
      {
        title: "First Aid & Oximeter Monitoring",
        description: "Comprehensive medical kit, pulse oximeter for daily oxygen saturation checks, and emergency coordination.",
        category: "Safety",
        icon: "🩺"
      }
    ],

    // 11. Excluded Services
    excluded: [
      {
        title: "International Airfare & Visa",
        description: "International flights to/from Kathmandu and Nepal tourist entry visa fees.",
        category: "Documents",
        icon: "✈️"
      },
      {
        title: "Travel & Emergency Rescue Insurance",
        description: "Mandatory emergency medical evacuation and helicopter rescue insurance.",
        category: "Insurance",
        icon: "🚁"
      },
      {
        title: "Personal Beverages & Snacks",
        description: "Alcoholic drinks, bottled sodas, mineral water, and personal snack purchases.",
        category: "Meals",
        icon: "☕"
      },
      {
        title: "Teahouse Extras",
        description: "Hot bucket showers, device battery charging, and Wi-Fi cards at mountain lodges.",
        category: "Lodge",
        icon: "🔋"
      },
      {
        title: "Tips / Gratuities",
        description: "Customary gratuities for trekking guides, porters, and transport drivers.",
        category: "Staff",
        icon: "💵"
      },
      {
        title: "Personal Trekking Equipment",
        description: "Down jackets, sleeping bags, hiking boots, and personal clothing.",
        category: "Equipment",
        icon: "🥾"
      }
    ],

    // 12. Permits & Visa Information
    permitsAndVisa: {
      visaRequired: true,
      visaType: "Nepal Tourist Visa on Arrival",
      visaInformation: "Available upon arrival at Tribhuvan International Airport in Kathmandu ($30 USD for 15 days, $50 USD for 30 days). Ensure your passport has at least 6 months validity.",
      permitRequired: true,
      permits: [
        {
          name: "Langtang National Park Entry Permit",
          authority: "Department of National Parks & Wildlife Conservation",
          cost: 30,
          currency: "USD",
          includedInPrice: true,
          howToObtain: "Arranged directly by our operations team in Kathmandu."
        },
        {
          name: "TIMS Card (Trekkers' Information Management System)",
          authority: "Nepal Tourism Board & TAAN",
          cost: 20,
          currency: "USD",
          includedInPrice: true,
          howToObtain: "Arranged directly by our operations team prior to departure."
        }
      ]
    },

    // 13. Health, Safety & Emergency Protocols
    safety: {
      healthLevel: "Good physical fitness with cardiovascular endurance",
      difficulty: "Moderate",
      altitudeRisk: "Moderate (Maximum sleeping altitude 3,870m, viewpoint 4,773m)",
      acclimatizationDays: 1,
      insuranceRequired: true,
      insuranceInformation: "Comprehensive travel insurance covering high-altitude trekking up to 5,000m and emergency helicopter medical evacuation is mandatory.",
      emergencyContact: "+977 98510 00000 (24/7 Operations Hotline)",
      rescueProvider: "Heli Rescue Nepal / Global Rescue Partner",
      evacuationInformation: "Emergency helicopter evacuation can be initiated from Kyanjin Gompa and Langtang Village with rapid transport to CIWEC Clinic or Norvic Hospital in Kathmandu.",
      nearestHospital: "Dhunche District Hospital (road access) & Kathmandu Specialty Clinics (heli-evac)"
    },

    // 14. Dynamic Packing List
    packingList: [
      { item: "Sturdy Broken-in Hiking Boots", category: "Footwear", requiredLevel: "Required", quantity: "1 pair" },
      { item: "Thermal Base Layers (Merino Wool/Synthetic)", category: "Clothing", requiredLevel: "Required", quantity: "2-3 sets" },
      { item: "Fleece Mid-Layer / Windstopper Jacket", category: "Clothing", requiredLevel: "Required", quantity: "1-2 pcs" },
      { item: "Down Jacket (-10°C to -15°C rated)", category: "Clothing", requiredLevel: "Required", quantity: "1 pc" },
      { item: "Waterproof Gore-Tex Shell Jacket & Pants", category: "Clothing", requiredLevel: "Required", quantity: "1 set" },
      { item: "Four-Season Sleeping Bag (-10°C comfort)", category: "Sleeping", requiredLevel: "Recommended", quantity: "1 pc" },
      { item: "Trekking Poles with Rubber Tips", category: "Equipment", requiredLevel: "Recommended", quantity: "1 pair" },
      { item: "Water Purification Tablets / UV Filter", category: "Hydration", requiredLevel: "Required", quantity: "1 set" },
      { item: "UV400 Polarized Glacier Sunglasses", category: "Personal", requiredLevel: "Required", quantity: "1 pair" },
      { item: "High-Capacity Power Bank (20,000mAh)", category: "Electronics", requiredLevel: "Recommended", quantity: "1 pc" }
    ],

    // 15. Essential Information Accordions
    essentialInfo: [
      {
        id: "info-lodges",
        title: "Lodge & Teahouse Conditions in Langtang",
        content: "Teahouses in the Langtang Valley are simple, clean, and run by local Tamang families. Twin bedrooms have foam mattresses, pillows, and blankets (we also recommend bringing your own sleeping bag for sub-zero alpine nights). Dining halls are heated in the evening with central wood-fired stoves. Electricity is solar or hydro powered; charging and hot bucket showers are available for a small fee (200-400 NPR)."
      },
      {
        id: "info-meals",
        title: "Food & Drinking Water on the Trail",
        content: "Meals are freshly prepared at each lodge. You will enjoy nutritious traditional Dal Bhat (lentil soup with steamed rice, seasonal vegetable curry, and pickles - with free refills), hearty noodle soups (Thukpa), Tibetan bread, porridge, eggs, momos, and fresh yak cheese. We recommend drinking boiled or purified water; carry reusable water bottles and purification drops to minimize plastic waste in the national park."
      },
      {
        id: "info-altitude",
        title: "Altitude Acclimatization & Safety",
        content: "Our 10-day itinerary follows a carefully graduated altitude ascent profile. We sleep no higher than 3,870m at Kyanjin Gompa and include a dedicated exploration day before attempting high viewpoints. Your lead guide carries a pulse oximeter and performs daily checks of oxygen saturation and pulse rates. If symptoms of Acute Mountain Sickness (AMS) develop, the standard protocol is immediate rest or descent."
      },
      {
        id: "info-rescue",
        title: "Helicopter Evacuation & Insurance Requirement",
        content: "You must provide proof of comprehensive travel insurance before starting the trek. The policy MUST explicitly state emergency medical evacuation and helicopter airlift coverage up to at least 5,000 meters. In the rare event of severe altitude sickness or trauma, our 24/7 operations team coordinates immediate helicopter dispatch from Kathmandu."
      },
      {
        id: "info-porter",
        title: "Porter Policy & Weight Limits",
        content: "We strictly adhere to ethical porter treatment guidelines endorsed by the International Porter Protection Group (IPPG). Our porters carry a maximum of 30kg shared between two trekkers (15kg maximum duffel weight per client). Porters are provided with adequate cold-weather clothing, medical insurance, fair wages, and proper lodge accommodations."
      }
    ],

    // 16. Documents & Downloads
    documents: [
      {
        title: "Langtang Trek Detailed Route Dossier (PDF)",
        fileUrl: "#",
        fileType: "PDF",
        fileSize: "2.4 MB",
        visibility: "public",
        description: "Complete day-by-day route notes, elevation profile maps, and regional background."
      },
      {
        title: "Himalayan Gear & Packing Checklist (PDF)",
        fileUrl: "#",
        fileType: "PDF",
        fileSize: "1.1 MB",
        visibility: "public",
        description: "Official recommended gear checklist with seasonal variations."
      },
      {
        title: "Internal Operations & Guide Protocol (Staff Only)",
        fileUrl: "#",
        fileType: "PDF",
        fileSize: "850 KB",
        visibility: "internal",
        description: "Emergency contacts, local lodge supplier contacts, and guide emergency guidelines."
      }
    ],

    // 17. Responsive Gallery
    gallery: [
      {
        id: "gal-1",
        url: "https://images.unsplash.com/photo-1544735716-392fe2489ffa?auto=format&fit=crop&w=1200&q=80",
        thumbnail: "https://images.unsplash.com/photo-1544735716-392fe2489ffa?auto=format&fit=crop&w=400&q=80",
        alt: "Snow peaks rising over the sacred Langtang valley",
        caption: "Glacial valley of Kyanjin Gompa beneath Langtang Lirung (7,227m)",
        category: "Mountain"
      },
      {
        id: "gal-2",
        url: "https://images.unsplash.com/photo-1486870591958-9b9d0d1dda99?auto=format&fit=crop&w=1200&q=80",
        thumbnail: "https://images.unsplash.com/photo-1486870591958-9b9d0d1dda99?auto=format&fit=crop&w=400&q=80",
        alt: "Morning alpine light hitting high Himalayan ridges",
        caption: "Sunrise illumination on Dorje Lakpa and surrounding peaks",
        category: "Landscape"
      },
      {
        id: "gal-3",
        url: "https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=1200&q=80",
        thumbnail: "https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=400&q=80",
        alt: "Clear glacial river roaring through rhododendron forests",
        caption: "Langtang Khola rushing through ancient pine gorges",
        category: "Trail"
      },
      {
        id: "gal-4",
        url: "https://images.unsplash.com/photo-1517824806704-9040b037703b?auto=format&fit=crop&w=1200&q=80",
        thumbnail: "https://images.unsplash.com/photo-1517824806704-9040b037703b?auto=format&fit=crop&w=400&q=80",
        alt: "Tamang mountain village settlement",
        caption: "Stone lodges and prayer flags at New Langtang Village",
        category: "Culture"
      }
    ],

    // 18. Fixed Departures
    departures: [
      {
        id: "dep-2026-001",
        startDate: "15 Oct 2026",
        endDate: "24 Oct 2026",
        duration: "10 Days",
        price: 1190,
        currency: "USD",
        remainingSpaces: 6,
        status: "Available",
        bookingUrl: "#enquiry"
      },
      {
        id: "dep-2026-002",
        startDate: "02 Nov 2026",
        endDate: "11 Nov 2026",
        duration: "10 Days",
        price: 1190,
        currency: "USD",
        remainingSpaces: 2,
        status: "Limited",
        bookingUrl: "#enquiry"
      },
      {
        id: "dep-2027-003",
        startDate: "12 Mar 2027",
        endDate: "21 Mar 2027",
        duration: "10 Days",
        price: 1240,
        currency: "USD",
        remainingSpaces: 10,
        status: "Available",
        bookingUrl: "#enquiry"
      }
    ],

    // 20. Reviews (Sample Testimonials with Moderation Fields)
    reviews: [
      {
        id: "rev-01",
        name: "David & Sarah Miller",
        city: "Melbourne",
        country: "Australia",
        tourDate: "November 2025",
        rating: 5,
        title: "Spectacular scenery and deeply touching hospitality",
        review: "Langtang exceeded all our expectations. The climb up Kyanjin Ri at sunrise was simply unforgettable. Our guide Pemba was incredibly knowledgeable and attentive to our acclimatization throughout.",
        status: "Approved",
        featured: true
      },
      {
        id: "rev-02",
        name: "Elena Rostova",
        city: "Zurich",
        country: "Switzerland",
        tourDate: "October 2025",
        rating: 5,
        title: "A wonderful less-crowded alternative to the Annapurnas",
        review: "The valley is stunningly beautiful and the trail feels very peaceful compared to busier routes. The local yak cheese at Kyanjin Gompa was delicious and our porters were absolute champions.",
        status: "Approved",
        featured: false
      }
    ],

    // 21. Related Trips
    relatedTrips: [
      {
        id: "rel-01",
        title: "Annapurna Sanctuary & Base Camp Trek",
        country: "Nepal",
        category: "Himalayan Classic",
        duration: "12 Days",
        difficulty: "Moderate",
        price: 1350,
        currency: "USD",
        image: "https://images.unsplash.com/photo-1486870591958-9b9d0d1dda99?auto=format&fit=crop&w=600&q=80",
        url: "#"
      },
      {
        id: "rel-02",
        title: "Manaslu Circuit & Larke Pass Trek",
        country: "Nepal",
        category: "Wilderness Circuit",
        duration: "15 Days",
        difficulty: "Strenuous",
        price: 1780,
        currency: "USD",
        image: "https://images.unsplash.com/photo-1544735716-392fe2489ffa?auto=format&fit=crop&w=600&q=80",
        url: "#"
      }
    ],

    // 22. SEO Metadata
    seo: {
      seoTitle: "Langtang & Kyanjin Trek 10 Days | Nepal Himalayan Valley Journey",
      metaDescription: "Experience the magnificent Langtang Valley Trek in Nepal. 10 days of serene pine forests, Tamang heritage, and Kyanjin Gompa glaciers with certified guides.",
      focusKeyword: "Langtang Kyanjin Trek",
      canonicalUrl: "https://yetiadventures.com/treks/langtang-kyanjin-trek",
      socialTitle: "Langtang & Kyanjin Valley Trek - 10 Days in Nepal",
      socialDescription: "Explore Nepal's sacred Valley of Glaciers beneath 7,000m peaks.",
      socialImage: "https://images.unsplash.com/photo-1544735716-392fe2489ffa?auto=format&fit=crop&w=1200&q=80"
    },

    // 23. Internal Staff & Admin Audit Metadata
    internalNotes: "Verified permit rates with DNPWC for 2026/2027 season. Recommended driver contact: Ram Shrestha (Kathmandu 4WD Fleet). Supplier lodge contact in Kyanjin: Tenzing Norbu.",
    adminMetadata: {
      author: "Admin / Editorial Team",
      createdAt: "2026-01-15T09:00:00Z",
      updatedAt: "2026-08-21T13:00:00Z",
      lastUpdatedBy: "Senior Content Architect",
      revision: 14,
      dataConfidence: "High (Official Itinerary)",
      approvalStatus: "Approved"
    }
  };

  // Secondary Dataset: Annapurna Sanctuary (To Demonstrate 1-Click Multi-Destination Switching)
  const annapurnaData = {
    id: "trek-nepal-annapurna-002",
    title: "Annapurna Sanctuary & Base Camp",
    shortTitle: "Annapurna Base Camp",
    slug: "annapurna-sanctuary-trek",
    status: "published",
    featured: true,
    category: "Himalayan Classic",
    tripType: "Lodge & Tea House Trek",
    geography: {
      continent: "Asia",
      country: "Nepal",
      region: "Annapurna Himal",
      subRegion: "Kaski District",
      nationalPark: "Annapurna Conservation Area (ACAP)",
      gatewayCity: "Pokhara",
      startingPoint: "Nayapul / Jhinu Danda",
      endingPoint: "Jhinu Danda",
      trailhead: "Jhinu Danda (1,780m)",
      routeType: "Out and Back",
      routeDirection: "North into the Glacial Sanctuary",
      distance: 115,
      distanceUnit: "km",
      latitude: 28.5306,
      longitude: 83.8780,
      mapUrl: "https://images.unsplash.com/photo-1486870591958-9b9d0d1dda99?auto=format&fit=crop&w=1200&q=80"
    },
    hero: {
      eyebrow: "Into the Heart of the Giants",
      title: "Annapurna Sanctuary & Base Camp",
      subtitle: "Hike deep into the legendary glacial amphitheater surrounded by Annapurna I (8,091m), Machapuchare ('Fishtail'), and Hiunchuli.",
      image: "https://images.unsplash.com/photo-1486870591958-9b9d0d1dda99?auto=format&fit=crop&w=1920&q=85",
      imageAlt: "Machapuchare Fishtail Peak towering over rhododendron forests",
      video: null,
      primaryCTA: { label: "Book Departure", url: "#departures" },
      secondaryCTA: { label: "View Itinerary", url: "#itinerary" }
    },
    keyFacts: {
      duration: 12,
      nights: 11,
      country: "Nepal",
      region: "Annapurna Himal",
      highestPoint: 4130,
      highestSleepingAltitude: 4130,
      lowestAltitude: 820,
      trekkingDays: 9,
      difficulty: "Moderate to Strenuous",
      tripStyle: "Mountain Lodges",
      groupSize: "2 - 14 Travellers",
      bestSeason: "Oct–Dec & Mar–May",
      distance: 115
    },
    overview: {
      intro: "The Annapurna Base Camp (ABC) trek is universally celebrated as one of the world's premier mountain journeys. Trekking through bamboo groves and Gurung villages, the trail culminates at 4,130m inside a natural glacial amphitheater.",
      highlightQuote: "“Standing at Annapurna Base Camp with 360-degree snow walls towering 4,000 meters straight above is a humbling experience of pure alpine majesty.”",
      routeDescription: "Starting from Pokhara, the route traverses Modi Khola gorge, ascending past Machapuchare Base Camp to Annapurna Base Camp before descending via hot springs at Jhinu Danda.",
      destinationCharacter: "Spectacular 360-degree mountain amphitheater, traditional Gurung villages, and soothing natural hot springs."
    },
    highlights: [
      {
        id: "hl-abc-1",
        number: 1,
        title: "Annapurna Base Camp (4,130m)",
        description: "A 360-degree amphitheater facing the south face of Annapurna I (8,091m) and Annapurna South.",
        image: "https://images.unsplash.com/photo-1486870591958-9b9d0d1dda99?auto=format&fit=crop&w=600&q=80",
        imageAlt: "Snow peaks surrounding Annapurna Base Camp"
      },
      {
        id: "hl-abc-2",
        number: 2,
        title: "Machapuchare 'Fishtail' Sacred Peak",
        description: "Unparalleled views of Nepal's most iconic unclimbed holy mountain from Machapuchare Base Camp (3,700m).",
        image: "https://images.unsplash.com/photo-1544735716-392fe2489ffa?auto=format&fit=crop&w=600&q=80",
        imageAlt: "Machapuchare Fishtail spire"
      }
    ],
    itinerary: [
      {
        dayNumber: 1,
        title: "Arrival in Kathmandu",
        startLocation: "Kathmandu Airport",
        endLocation: "Kathmandu Hotel",
        activityTypes: ["Arrival"],
        walkingHours: 0,
        elevationStart: 1400,
        elevationEnd: 1400,
        highestAltitude: 1400,
        accommodationName: "Kathmandu Boutique Hotel",
        mealPlan: "Welcome Dinner",
        description: "Meet your guide and transfer to your hotel. Welcome briefing and equipment check in the evening."
      },
      {
        dayNumber: 2,
        title: "Fly to Pokhara & Drive to Trailhead",
        startLocation: "Kathmandu",
        endLocation: "Ulleri (2,050m)",
        activityTypes: ["Flight", "Drive", "Trek"],
        walkingHours: 3,
        elevationStart: 1400,
        elevationEnd: 2050,
        highestAltitude: 2050,
        accommodationName: "Ulleri Mountain Lodge",
        mealPlan: "Breakfast, Lunch, Dinner",
        description: "Scenic 25-minute flight past the Himalayan chain to Pokhara, followed by drive to trailhead and introductory afternoon trek."
      }
    ],
    accommodation: [
      {
        propertyName: "Pokhara Lakeside Resort",
        propertyType: "Hotel",
        location: "Pokhara",
        nights: 2,
        roomType: "Lake View Ensuite",
        mealBasis: "Bed & Breakfast",
        bathroomType: "Private Ensuite",
        description: "Tranquil resort situated by Phewa Lake in Pokhara.",
        image: "https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=600&q=80"
      }
    ],
    pricing: {
      priceType: "from",
      price: 1350,
      currency: "USD",
      adultPrice: 1350,
      childPrice: 1100,
      singleSupplement: 220,
      deposit: 300,
      priceNotes: "Includes domestic Pokhara flights and ACAP permits."
    },
    included: [
      { title: "Domestic Flights", description: "Kathmandu - Pokhara roundtrip flights.", category: "Transportation", icon: "✈️" },
      { title: "ACAP & TIMS Permits", description: "Annapurna Conservation Area entry fees.", category: "Permits", icon: "📄" },
      { title: "Mountain Guide & Porters", description: "Licensed lead guide and porter crew.", category: "Guide", icon: "🧗" }
    ],
    excluded: [
      { title: "International Flights", description: "Flights to/from Nepal.", category: "Documents", icon: "✈️" },
      { title: "Helicopter Evacuation Insurance", description: "Mandatory travel insurance.", category: "Insurance", icon: "🚁" }
    ],
    safety: {
      difficulty: "Moderate to Strenuous",
      altitudeRisk: "High (Base camp reaches 4,130m)",
      emergencyContact: "+977 98510 00000",
      insuranceRequired: true
    },
    packingList: [
      { item: "Waterproof Hiking Boots", category: "Footwear", requiredLevel: "Required" },
      { item: "Four-Season Down Jacket", category: "Clothing", requiredLevel: "Required" }
    ],
    essentialInfo: [
      {
        id: "info-abc-altitude",
        title: "High Altitude in the Sanctuary",
        content: "Altitude is gained gradually from Pokhara. At ABC (4,130m), temperatures can drop below freezing even in autumn and spring."
      }
    ],
    documents: [
      { title: "Annapurna Route Map (PDF)", fileUrl: "#", fileType: "PDF", visibility: "public" }
    ],
    gallery: [
      {
        id: "gal-abc-1",
        url: "https://images.unsplash.com/photo-1486870591958-9b9d0d1dda99?auto=format&fit=crop&w=1200&q=80",
        caption: "Annapurna South sunrise glow",
        category: "Mountain"
      }
    ],
    departures: [
      {
        id: "dep-abc-001",
        startDate: "20 Oct 2026",
        endDate: "31 Oct 2026",
        duration: "12 Days",
        price: 1350,
        currency: "USD",
        remainingSpaces: 4,
        status: "Available"
      }
    ],
    reviews: [
      {
        id: "rev-abc-1",
        name: "Markus Becker",
        city: "Munich",
        country: "Germany",
        tourDate: "October 2025",
        rating: 5,
        title: "Unreal scenery inside the sanctuary",
        review: "Standing at base camp surrounded by 8000m giants was a dream come true.",
        status: "Approved"
      }
    ],
    relatedTrips: [],
    seo: {
      seoTitle: "Annapurna Base Camp Trek 12 Days | Sanctuary Route Nepal",
      metaDescription: "Trek to Annapurna Base Camp (4,130m) with expert guides.",
      focusKeyword: "Annapurna Base Camp Trek"
    }
  };

  // Expose sample datasets globally
  window.TREK_SAMPLE_DATASETS = {
    'langtang-kyanjin-trek': langtangData,
    'annapurna-sanctuary-trek': annapurnaData
  };

  // Default active dataset
  window.TREK_DATA = langtangData;

})(window);
