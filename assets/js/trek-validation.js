/**
 * Trek Content Manager - Data Quality & Cross-Field Consistency Engine
 * 
 * Audits trek datasets for:
 * 1. Completeness percentage
 * 2. Cross-field consistency (duration vs itinerary days, declared altitude vs day altitudes, etc.)
 * 3. Required regulatory/safety data (permits, emergency protocols, rescue contacts)
 */

(function(window) {
  'use strict';

  const TrekValidation = {
    // Required core fields for a complete publishable destination
    REQUIRED_FIELDS: [
      { key: 'title', label: 'Trek Title', weight: 10 },
      { key: 'geography.country', label: 'Country / Geography', weight: 10 },
      { key: 'geography.region', label: 'Region / Mountain Range', weight: 8 },
      { key: 'hero.image', label: 'High-Res Hero Image', weight: 10 },
      { key: 'hero.subtitle', label: 'Hero Subtitle / Kicker', weight: 6 },
      { key: 'keyFacts.duration', label: 'Declared Duration', weight: 8 },
      { key: 'keyFacts.highestPoint', label: 'Highest Altitude Fact', weight: 8 },
      { key: 'overview.intro', label: 'Editorial Introduction', weight: 10 },
      { key: 'itinerary', label: 'Day-by-Day Itinerary', weight: 15, isArray: true, minItems: 1 },
      { key: 'included', label: 'Cost Inclusions List', weight: 5, isArray: true, minItems: 1 },
      { key: 'excluded', label: 'Cost Exclusions List', weight: 5, isArray: true, minItems: 1 },
      { key: 'safety.evacuationInformation', label: 'Helicopter Rescue & Evacuation Info', weight: 5 }
    ],

    /**
     * Audit full trek dataset and return score, issues, and warnings
     */
    auditTrek: function(trekData) {
      if (!trekData) {
        return { score: 0, issues: [{ severity: 'error', message: 'No trek data loaded' }], isValid: false };
      }

      const issues = [];
      let earnedWeight = 0;
      let totalWeight = 0;

      // 1. Check Required Fields & Completeness
      this.REQUIRED_FIELDS.forEach(field => {
        totalWeight += field.weight;
        const val = this.getNestedValue(trekData, field.key);

        if (field.isArray) {
          if (Array.isArray(val) && val.length >= (field.minItems || 1)) {
            earnedWeight += field.weight;
          } else {
            issues.push({
              severity: 'error',
              field: field.key,
              message: `Missing required field: ${field.label}`
            });
          }
        } else {
          if (val !== undefined && val !== null && String(val).trim() !== '') {
            earnedWeight += field.weight;
          } else {
            issues.push({
              severity: 'error',
              field: field.key,
              message: `Missing required field: ${field.label}`
            });
          }
        }
      });

      const completenessScore = Math.round((earnedWeight / totalWeight) * 100);

      // 2. Cross-Field Consistency Checks

      // A. Duration vs Itinerary Day Count
      const declaredDuration = Number(trekData.keyFacts?.duration || 0);
      const actualItineraryDays = Array.isArray(trekData.itinerary) ? trekData.itinerary.length : 0;

      if (declaredDuration > 0 && actualItineraryDays > 0) {
        if (declaredDuration !== actualItineraryDays) {
          issues.push({
            severity: 'warning',
            field: 'itinerary',
            message: `Duration Discrepancy: Declared duration is ${declaredDuration} days, but itinerary contains ${actualItineraryDays} days.`
          });
        }
      }

      // B. Declared Highest Point vs Maximum Day Altitude
      const declaredHighest = Number(trekData.keyFacts?.highestPoint || 0);
      if (Array.isArray(trekData.itinerary) && declaredHighest > 0) {
        let maxItineraryAltitude = 0;
        trekData.itinerary.forEach(day => {
          const high = Number(day.highestAltitude || day.elevationEnd || 0);
          if (high > maxItineraryAltitude) {
            maxItineraryAltitude = high;
          }
        });

        if (maxItineraryAltitude > 0 && Math.abs(declaredHighest - maxItineraryAltitude) > 50) {
          issues.push({
            severity: 'warning',
            field: 'keyFacts.highestPoint',
            message: `Altitude Inconsistency: Quick Fact declares highest point as ${declaredHighest.toLocaleString()}m, but Itinerary peaks at ${maxItineraryAltitude.toLocaleString()}m.`
          });
        }
      }

      // C. Declared Trekking Days vs Itinerary Trek Activities
      const declaredTrekDays = Number(trekData.keyFacts?.trekkingDays || 0);
      if (Array.isArray(trekData.itinerary) && declaredTrekDays > 0) {
        const actualTrekActivities = trekData.itinerary.filter(d => 
          (d.activityTypes && (d.activityTypes.includes('Trek') || d.activityTypes.includes('Walk')))
        ).length;

        if (actualTrekActivities > 0 && declaredTrekDays !== actualTrekActivities) {
          issues.push({
            severity: 'info',
            field: 'keyFacts.trekkingDays',
            message: `Trekking Days Note: Declared trekking days is ${declaredTrekDays}, while itinerary lists ${actualTrekActivities} trekking activity days.`
          });
        }
      }

      // D. Fixed Departures Check
      if (!Array.isArray(trekData.departures) || trekData.departures.length === 0) {
        issues.push({
          severity: 'info',
          field: 'departures',
          message: `No upcoming fixed departure dates currently scheduled.`
        });
      }

      // E. Emergency Contact & Safety Check
      if (!trekData.safety?.emergencyContact && !trekData.safety?.rescueProvider) {
        issues.push({
          severity: 'warning',
          field: 'safety',
          message: `Safety Alert: No explicit emergency contact or rescue coordinator specified.`
        });
      }

      return {
        score: completenessScore,
        issues: issues,
        isValid: issues.filter(i => i.severity === 'error').length === 0
      };
    },

    getNestedValue: function(obj, path) {
      return path.split('.').reduce((acc, part) => acc && acc[part], obj);
    }
  };

  window.TrekValidation = TrekValidation;
})(window);
