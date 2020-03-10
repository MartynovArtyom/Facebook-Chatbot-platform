import { Component, Input, OnInit } from '@angular/core';
import { AudienceService } from '../../../../services/audience.service';
import { SharedService } from '../../../../services/shared.service';
import { BuilderService } from '../../../../modules/messages-builder/services/builder.service';

@Component({
  selector: 'app-user-choise-condition-option',
  templateUrl: './user-choise-condition-option.component.html',
  styleUrls: ['./user-choise-condition-option.component.scss']
})
export class UserChoiseConditionOptionComponent implements OnInit {

  public _statusDraft: any;
  public _view: any;

  @Input('statusDraft') set statusDraft(statusDraft) {
    if (statusDraft) {
      this._statusDraft = statusDraft;
    }
  }
  get statusDraft() {
    return this._statusDraft;
  }

  @Input('view') set view(view) {
    if (view) {
      this._view = view;
    }
  }
  get view() {
    return this._view;
  }

  public statusDelete = true;
  public widgetCount = 1050;

  constructor(
    public readonly _sharedService: SharedService,
    public readonly _builderService: BuilderService,
    public readonly _audienceService: AudienceService
  ) { }

  ngOnInit() {
  }

  /**
   * Delete change event
   */
  public statusDeleteChange() {
    this.statusDelete = false;
    setTimeout(() => {
      this.statusDelete = true;
    }, 100);
  }

  /**
   * Delete condition item
   * @param index
   */
  public deleteCondition(index) {
    this._sharedService.conditionArray.splice(index, 1);
    this._audienceService.setOptionToConditionFilter(this._statusDraft);
    setTimeout(() => {
      this._builderService.getPointsPosition(this._builderService.config, this._builderService.elem);
    }, 10);
  }

  /**
   * Choise condition item
   * @param condition {object}
   */
  public choiceCondition(condition) {

    if (condition.customFieldID) {
      this._audienceService.createValueConditionList('none');
      if (condition.type === 1) {
        this._audienceService.criteriaForFilters(['is', 'isn\'t', 'contains', 'not_contains']);
      } else if (condition.type === 2) {
        this._audienceService.criteriaForFilters(['is', 'isn\'t', 'greater_than', 'less_than']);
      } else if (condition.type === 3 || condition.type === 4) {
        this._audienceService.criteriaForFilters(['after', 'before', 'on']);
      } else if (condition.type === 5) {
        this._audienceService.criteriaForFilters(['is']);
      }
    } else if (condition.tagID || condition.widgetID || condition.sequenceID) {
      this._audienceService.createValueConditionList('none');
      this._audienceService.criteriaForFilters(['is', 'isn\'t']);
    } else if (typeof condition.name === 'string') {
      switch (condition.name) {
        case 'status':
          this._audienceService.criteriaForFilters(['is']);
          this._audienceService.createValueConditionList('status');
          break;
        case 'gender':
          this._audienceService.criteriaForFilters(['is']);
          this._audienceService.createValueConditionList('gender');
          break;
        case 'locale':
          this._audienceService.criteriaForFilters(['is', 'isn\'t']);
          this._audienceService.createValueConditionList('locale');
          break;
        case 'language':
          this._audienceService.criteriaForFilters(['is', 'isn\'t']);
          this._audienceService.createValueConditionList('language');
          break;
        case 'timezone':
          this._audienceService.criteriaForFilters(['is', 'isn\'t', 'greater_than', 'less_than']);
          this._audienceService.createValueConditionList('timezone');
          break;
        case 'firstName':
          this._audienceService.criteriaForFilters(['is', 'isn\'t', 'contains', 'not_contains']);
          this._audienceService.createValueConditionList('firstName');
          break;
        case 'lastName':
          this._audienceService.criteriaForFilters(['is', 'isn\'t', 'contains', 'not_contains']);
          this._audienceService.createValueConditionList('lastName');
          break;
        case 'dateSubscribed':
          this._audienceService.criteriaForFilters(['after', 'before', 'on']);
          this._audienceService.createValueConditionList('dateSubscribed');
          break;
        case 'lastInteraction':
          this._audienceService.criteriaForFilters(['after', 'before', 'on']);
          this._audienceService.createValueConditionList('lastInteraction');
          break;
      }
    }
    this._sharedService.noneConditionButton = false;
    this._sharedService.activeCondition = false;
  }

  /**
   * Open/close condition popup
   * @param status
   * @param i
   */
  public activeConditionPopup(status, i) {
    setTimeout(() => {
      status = !status;
      this.choiceCondition(this._sharedService.conditionArray[i]);
      this._sharedService.conditionArray[i].opened = status;
    }, 100);
  }

  /**
   * Outside clicked
   * @param e {Event}
   * @param status {boolean}
   * @param i {number}
   */
  public onClickedOutside(e: Event, status, i) {
    this._sharedService.conditionArray[i].opened = false;
    if (!this._sharedService.conditionArray[i].value) {
      this._sharedService.conditionArray.pop();
      setTimeout(() => {
        this._builderService.getPointsPosition(this._builderService.config, this._builderService.elem);
      }, 10);
    }
  }

  /**
   * Add definition to condition items
   * @param value
   * @param i
   */
  public addToConditionArrayDefinition(value, i) {
    this._sharedService.conditionArray[i].criteria = value;
    if (this._sharedService.conditionArray[i].value) {
      this._audienceService.setOptionToConditionFilter(this._statusDraft);
    }
  }

  /**
   * Add description to conditions items
   * @param value {string}
   * @param i {number}
   */
  public addToConditionArrayDescription(value, i) {
    this._sharedService.conditionArray[i].opened = false;
    if (value.tagID) {
      this._sharedService.conditionArray[i].tagID = value.tagID;
      this._sharedService.conditionArray[i].value = value.name;
      this._sharedService.conditionArray[i].conditionType = 'tag';
    } else if (value.sequenceID) {
      this._sharedService.conditionArray[i].sequenceID = value.sequenceID;
      this._sharedService.conditionArray[i].value = value.name;
      this._sharedService.conditionArray[i].conditionType = 'sequence';
    } else if (value.widgetID) {
      this._sharedService.conditionArray[i].widgetID = value.widgetID;
      this._sharedService.conditionArray[i].value = value.name;
      this._sharedService.conditionArray[i].conditionType = 'widget';
    } else if (value.customFieldID) {
      this._sharedService.conditionArray[i].conditionType = 'customField';
    } else if (value === 'true' || value === 'false') {
      this._sharedService.conditionArray[i].conditionType = 'customField';
      this._sharedService.conditionArray[i].value = value;
    } else {
      this._sharedService.conditionArray[i].value = value;
      this._sharedService.conditionArray[i].conditionType = 'system';
    }
    this._audienceService.setOptionToConditionFilter(this._statusDraft);
    setTimeout(() => {
      this._builderService.getPointsPosition(this._builderService.config, this._builderService.elem);
    }, 10);
  }

}
