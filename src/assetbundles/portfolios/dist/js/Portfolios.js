/**
 * Portfolios plugin for Craft CMS
 *
 * Portfolios JS
 *
 * @author    Niklas Sonnenschein
 * @copyright Copyright (c) 2019 Niklas Sonnenschein
 * @link      https://niklassonnenschein.de
 * @package   Portfolios
 * @since     1.0.0
 */

if (typeof Portfolios == "undefined") {
  Portfolios = {};
}

Portfolios.Explorer = Garnish.Base.extend({
  $container: null,
  $explorer: null,
  $modal: null,
  $main: null,
  $mainContent: null,
  $projects: null,
  $projectElements: null,
  $sectionLinks: null,
  $spinner: null,
  $search: null,

  serchTimeout: null,

  init: function($container, settings) {
    this.$container = $container;
    this.settings = settings;

    const data = {
      namespaceInputId: this.settings.namespaceInputId
    };

    $('<div class="spinner"/>').appendTo(this.$container);

    Craft.postActionRequest("portfolios/explorer/get-modal", data, $.proxy(function(response, textStatus) {
      var errorMessage,
          $error;

      if (textStatus == "success") {
        if (response.success) {
          this.$modal = $(response.html);
          this.$container.html(this.$modal);

          this.$main = $(".main", this.$modal);
          this.$spinner = $(".spinner", this.$modal);
          this.$sectionLinks = $("nav a", this.$modal);
          this.$search = $(".search", this.$modal);
          this.$mainContent = $(".main .explorer-content", this.$modal);
          this.$explorer = $(".portfolios-explorer", this.$container);
          this.$projects = $(".projects", this.$modal);

          this.$sectionLinks.on("click", $.proxy(this.handleSectionClick, this));
          this.$search.on("textchange", $.proxy(this.handleSearchChange, this));
          this.$search.on("blur", $.proxy(this.handleSearchBlur, this));
          this.$search.on("keypress", $.proxy(this.handleSearchKeypress, this))

          Craft.initUiElements();

          $("nav a:first", this.$modal).trigger("click");
        } else {
          errorMessage = "Projekte Manager konnte nicht geladen werden.";

          if(response.error) {
            errorMessage = response.error;
          }

          $error = $(`<div class="error">${errorMessage}</div>`);
          $error.appendTo(this.$container);
        }
      } else {
        errorMessage = "Projekte Manager konnte nicht geladen werden.";
        $error = $(`<div class="error">${errorMessage}</div>`);
        $error.appendTo(this.$container);
      }
    }, this));
  },

  handleSearchChange: function(e) {
    if (this.searchTimeout) {
      clearTimeout(this.searchTimeout);
    }

    this.searchTimeout = setTimeout($.proxy(this, 'search', e), 500);
  },

  handleSearchBlur: function(e) {
    const q = $(e.currentTarget).val();

    if (q.length == 0) {
      this.$sectionLinks.filter(".sel").trigger("click");
    }
  },

  handleSearchKeypress: function(e) {
    if (e.keyCode == Garnish.RETURN_KEY) {
      e.preventDefault();
      this.search(e);
    }
  },

  handleSectionClick: function(e) {
    this.$sectionLinks.filter(".sel").removeClass("sel");
    $(e.currentTarget).addClass("sel");

    const gateway = $(e.currentTarget).data("gateway");
    this.getProjects(gateway);

    e.preventDefault();
  },

  search: function(e) {
    const q = $(e.currentTarget).val();

    if (q.length > 0) {
      const gateway = this.$sectionLinks.filter(".sel").attr("data-gateway");
      const method = 'search';
      const options = {
        q: q
      };

      this.getProjects(gateway, method, options)
    }
  },

  getProjects: function(gateway, method, options) {
    method = method || "get";
    options = options || {};

    const data = {
      gateway: gateway,
      method: method,
      options: options
    };

    this.$spinner.removeClass("invisible");

    Craft.postActionRequest("portfolios/explorer/get-projects", data, $.proxy(function(response, textStatus) {
      this.deselectProjects();
      this.$spinner.addClass("invisible");
      this.$projects.html("");

      if (textStatus=="success") {
        if (response.error) {
          this.$mainContent.html(`<p class="error">${response.error}</p>`);
        } else {
          $(".error", this.$mainContent).remove();

          this.$projects = $('<div class="projects" />');
          this.$projects.html(response.html);

          this.$mainContent.append(this.$projects);
          this.$projectElements = $(".element", this.$projects);

          this.$projectElements.on("click", $.proxy(this.selectProject, this));
          this.$projectElements.on("dblclick", $.proxy(this.dblClickProject, this));
        }
      } else {
        this.$mainContent.html('<p class="error">Projekte konnten nicht geladen werden.</p>');
      }

      $(".main", this.$modal).animate({scrollTop:0}, 0);

    }, this));
  },

  selectProject: function(e) {
    this.$projectElements.removeClass("sel");
    $(e.currentTarget).addClass("sel");

    const url = $(e.currentTarget).data("url");
    this.settings.onSelectProject(url);
  },

  dblClickProject: function(e) {
    this.selectProject(e);
    
    const url = $(e.currentTarget).data("url");
    this.settings.onDoubleClickProject(url);
  },

  deselectProjects: function() {
    if (this.$projectElements) {
      const currentProjects = this.$projectElements.filter(".sel");
      currentProjects.removeClass(".sel");

      this.settings.onDeselectProject();
    }
  }
});
