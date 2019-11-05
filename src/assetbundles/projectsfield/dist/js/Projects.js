/**
 * Portfolios plugin for Craft CMS
 *
 * Projects Field JS
 *
 * @author    Niklas Sonnenschein
 * @copyright Copyright (c) 2019 Niklas Sonnenschein
 * @link      https://niklassonnenschein.de
 * @package   Portfolios
 * @since     1.0.0PortfoliosProjects
 */

if (typeof Portfolios == 'undefined') {
  Portfolios = {};
}

Portfolios.Field = Garnish.Base.extend({
  $container: null,
  $addButtton: null,

  inputId: null,
  explorer: null,
  explorerHtml: null,
  projectSelectorModal: null,

  init: function (inputId) {
    this.inputId = inputId;
    this.$container = $('#' + inputId);
    this.$addButton = $(".add-project", this.$container);
    this.$table     = $("table", this.$container);

    this.$addButton.on('click', $.proxy(function(e){
      if(!this.projectSelectorModal) {
        this.openExplorer(e);
      } else {
        this.projectSelectorModal.show();
      }
    }, this));
  },

  openExplorer: function (e) {
    var   selectedProject;
    const $projectSelectorModal = $('<div class="projectselectormodal modal"></div>').appendTo(Garnish.$bod);
    const $explorerContainer = $('<div class="explorer-container"/>').appendTo($projectSelectorModal),
          $footer = $('<div class="footer"/>').appendTo($projectSelectorModal),
          $buttons = $('<div class="buttons right"/>').appendTo($footer),
          $cancelBtn = $('<div class="btn">' + Craft.t('app', 'Cancel') + '</div>').appendTo($buttons),
          $selectBtn = $('<input type="submit" class="btn submit disabled" value="' + Craft.t("app", "Select") + '">').appendTo($buttons);

    this.projectSelectorModal = new Garnish.Modal($projectSelectorModal, {
        visible: false,
        resizable: false
    });

    $cancelBtn.on('click', $.proxy(function() {
      this.projectSelectorModal.hide();
    }, this));

    $selectBtn.on('click', $.proxy(function() {
      this.appendTable(selectedProject);
      this.projectSelectorModal.hide();
    }, this));

    if (!this.explorer) {
      this.explorer = new Portfolios.Explorer($explorerContainer, {
        namespaceInputId: this.inputId,
        onSelectProject: $.proxy( (values) => {
          selectedProject = values;
          $selectBtn.removeClass("disabled");
        }, this),
        onDoubleClickProject: $.proxy( (values) => {
          this.appendTable(values);
          this.projectSelectorModal.hide();
        }, this),
        onDeselectProject: function() {
          $selectBtn.addClass("disabled");
        }
      });

      this.projectSelectorModal.updateSizeAndPosition();
      Craft.initUiElements();
    }
  },

  appendTable: function(values) {
    const project = values;
    const study = this.explorer.$sectionLinks.filter(".sel").html();

    var table = this.$table.data("editableTable"),
        rowId = table.settings.rowIdPrefix + (table.biggestId + 1),
        $tr   = table.createRow(rowId, table.columns, table.baseName, {
          "url": project.url,
          "title": project.title,
          "study": study,
          "semester": project.semester,
          "period": project.period,
          "year": project.year,
          "course": project.course,
          "img": project.img
        });

    $tr.appendTo(table.$tbody);

    var row = table.createRowObj($tr);
    table.sorter.addItems($tr);

    table.rowCount++;
    table.updateAddRowButton();
    table.settings.onAddRow($tr);
  }
});

/**
 * Matrix compatibility
 *
$(document).ready(function() {

    if(typeof(Matrix) != "undefined")
    {
        Matrix.bind("hfg_portfolios", "display", function(cell) {

            const $field = $('.input', this);

            // ignore if we can't find that field
            if (! $field.length) return;

            const fieldName = cell.field.id + '[' + cell.row.id + '][' + cell.col.id + ']',
                fieldId = fieldName.replace(/[^\w\-]+/g, '_');

            $field.attr('id', fieldId);

            cell.portfoliosField = new Portfolios.Field(fieldId);

        });
    }
});
*/
