/**
 * Trek Content Manager - Role, Permission & Data Scrubbing Engine
 * 
 * Provides granular capability matrix across 6 user roles:
 * - Administrator
 * - Content Manager
 * - Staff
 * - Editor
 * - Reviewer / Approver
 * - Traveller / Public Visitor
 * 
 * Includes client-side security data scrubber that strips internal/sensitive
 * metadata before rendering for public visitors.
 */

(function(window) {
  'use strict';

  const TrekRoles = {
    // Role Definitions & Default Permission Matrix
    ROLES: {
      admin: {
        id: 'admin',
        label: 'Administrator',
        permissions: {
          viewInternalNotes: true,
          editContent: true,
          editPricing: true,
          editSEO: true,
          approveReviews: true,
          publish: true,
          manageDepartures: true,
          viewRevisions: true,
          editPermissions: true,
          viewDataQuality: true,
          approveTrek: true,
          rejectTrek: true,
          exportData: true
        }
      },
      manager: {
        id: 'manager',
        label: 'Content Manager',
        permissions: {
          viewInternalNotes: true,
          editContent: true,
          editPricing: true,
          editSEO: true,
          approveReviews: true,
          publish: true,
          manageDepartures: true,
          viewRevisions: true,
          editPermissions: false,
          viewDataQuality: true,
          approveTrek: true,
          rejectTrek: true,
          exportData: true
        }
      },
      staff: {
        id: 'staff',
        label: 'Staff Member',
        permissions: {
          viewInternalNotes: false,
          editContent: true,
          editPricing: false,
          editSEO: false,
          approveReviews: false,
          publish: false,
          manageDepartures: false,
          viewRevisions: false,
          editPermissions: false,
          viewDataQuality: true,
          approveTrek: false,
          rejectTrek: false,
          exportData: false
        }
      },
      editor: {
        id: 'editor',
        label: 'Editor',
        permissions: {
          viewInternalNotes: false,
          editContent: true,
          editPricing: false,
          editSEO: true,
          approveReviews: false,
          publish: false,
          manageDepartures: false,
          viewRevisions: true,
          editPermissions: false,
          viewDataQuality: true,
          approveTrek: false,
          rejectTrek: false,
          exportData: false
        }
      },
      reviewer: {
        id: 'reviewer',
        label: 'Reviewer / Approver',
        permissions: {
          viewInternalNotes: false,
          editContent: false,
          editPricing: false,
          editSEO: false,
          approveReviews: true,
          publish: false,
          manageDepartures: false,
          viewRevisions: true,
          editPermissions: false,
          viewDataQuality: true,
          approveTrek: true,
          rejectTrek: true,
          exportData: false
        }
      },
      public: {
        id: 'public',
        label: 'Traveller / Public',
        permissions: {
          viewInternalNotes: false,
          editContent: false,
          editPricing: false,
          editSEO: false,
          approveReviews: false,
          publish: false,
          manageDepartures: false,
          viewRevisions: false,
          editPermissions: false,
          viewDataQuality: false,
          approveTrek: false,
          rejectTrek: false,
          exportData: false
        }
      }
    },

    // Check if role has a specific capability
    hasPermission: function(roleId, permissionKey) {
      const role = this.ROLES[roleId] || this.ROLES.public;
      return !!role.permissions[permissionKey];
    },

    /**
     * DATA SCRUBBER (Critical Security Layer)
     * When serving public users, cleanses all private staff notes, internal cost margins,
     * unapproved reviews, private documents, and administrative audit logs from memory.
     */
    scrubDataForRole: function(rawData, roleId, mode) {
      if (!rawData) return null;

      // Create a deep copy to prevent mutation of source data
      const data = JSON.parse(JSON.stringify(rawData));
      const role = this.ROLES[roleId] || this.ROLES.public;

      // If public role or published preview mode, purge all restricted fields
      if (role.id === 'public' || mode === 'published') {
        // Remove internal notes & author metadata
        delete data.internalNotes;
        delete data.adminMetadata;
        delete data.costBreakdownInternal;
        delete data.sourceAudit;

        // Filter reviews to strictly approved/featured
        if (Array.isArray(data.reviews)) {
          data.reviews = data.reviews.filter(r => r.status === 'Approved' || r.status === 'Featured');
        }

        // Filter documents to strictly public
        if (Array.isArray(data.documents)) {
          data.documents = data.documents.filter(d => d.visibility === 'public' || !d.visibility);
        }

        // Filter departures to active/available only
        if (Array.isArray(data.departures)) {
          data.departures = data.departures.filter(d => d.status !== 'Draft' && d.status !== 'Internal Only');
        }
      } else if (!role.permissions.viewInternalNotes) {
        // Staff/Editor without sensitive notes permission
        delete data.internalNotes;
        delete data.costBreakdownInternal;
      }

      return data;
    }
  };

  window.TrekRoles = TrekRoles;
})(window);
