import { Component, OnInit } from '@angular/core';
import { AudienceService } from '../../../../services/audience.service';
import { SharedService } from '../../../../services/shared.service';
import { BuilderService } from '../../../../modules/messages-builder/services/builder.service';

@Component({
  selector: 'app-user-condition-filter',
  templateUrl: './user-condition-filter.component.html',
  styleUrls: ['./user-condition-filter.component.scss']
})
export class UserConditionFilterComponent implements OnInit {

  public customUserFields = [];
  public tags = [
    {
      description: 'Tag',
      type: 'tags'
    },
    {
      description: 'Optin durch',
      type: 'widgets'
    },
    {
      description: 'Autoresponder',
      type: 'sequences'
    }
  ];
  public systemFields = {};

  constructor(
    public readonly _sharedService: SharedService,
    public readonly _builderService: BuilderService,
    public readonly _audienceService: AudienceService
  ) { }

  ngOnInit() {
    this.systemFields = this._audienceService.dataSubscribers.system;
    this.customUserFields = this._audienceService.dataSubscribers.customFields;
  }

  /**
   * Choise condition item
   * @param condition {object}
   */
  public choiceCondition(condition) {
    const dataPush = {
      opened: true
    };

    if (typeof condition === 'string') {
      switch (condition) {
        case 'status':
          dataPush['name'] = 'status';
          dataPush['criteria'] = 'is';
          this._audienceService.criteriaForFilters(['is']);
          this._audienceService.createValueConditionList('status');
          break;
        case 'gender':
          dataPush['name'] = 'gender';
          dataPush['criteria'] = 'is';
          this._audienceService.criteriaForFilters(['is']);
          this._audienceService.createValueConditionList('gender');
          break;
        case 'locale':
          dataPush['name'] = 'locale';
          dataPush['criteria'] = 'is';
          this._audienceService.criteriaForFilters(['is', 'isn\'t']);
          this._audienceService.createValueConditionList('locale');
          break;
        case 'language':
          dataPush['name'] = 'language';
          dataPush['criteria'] = 'is';
          this._audienceService.criteriaForFilters(['is', 'isn\'t']);
          this._audienceService.createValueConditionList('language');
          break;
        case 'timezone':
          dataPush['name'] = 'timezone';
          dataPush['criteria'] = 'is';
          this._audienceService.criteriaForFilters(['is', 'isn\'t', 'greater_than', 'less_than']);
          this._audienceService.createValueConditionList('timezone');
          break;
        case 'firstName':
          dataPush['name'] = 'firstName';
          dataPush['criteria'] = 'is';
          this._audienceService.criteriaForFilters(['is', 'isn\'t', 'contains', 'not_contains']);
          this._audienceService.createValueConditionList('firstName');
          break;
        case 'lastName':
          dataPush['name'] = 'lastName';
          dataPush['criteria'] = 'is';
          this._audienceService.criteriaForFilters(['is', 'isn\'t', 'contains', 'not_contains']);
          this._audienceService.createValueConditionList('lastName');
          break;
        case 'dateSubscribed':
          dataPush['name'] = 'dateSubscribed';
          dataPush['criteria'] = 'after';
          this._audienceService.criteriaForFilters(['after', 'before', 'on']);
          this._audienceService.createValueConditionList('dateSubscribed');
          break;
        case 'lastInteraction':
          dataPush['name'] = 'lastInteraction';
          dataPush['criteria'] = 'after';
          this._audienceService.criteriaForFilters(['after', 'before', 'on']);
          this._audienceService.createValueConditionList('lastInteraction');
          break;
      }
      dataPush['value'] = '';
    } else if (condition.customFieldID) {
      this._audienceService.createValueConditionList('none');
      if (condition.type === 1) {
        this._audienceService.criteriaForFilters(['is', 'isn\'t', 'contains', 'not_contains']);
        dataPush['criteria'] = 'is';
      } else if (condition.type === 2) {
        this._audienceService.criteriaForFilters(['is', 'isn\'t', 'greater_than', 'less_than']);
        dataPush['criteria'] = 'is';
      } else if (condition.type === 3 || condition.type === 4) {
        this._audienceService.criteriaForFilters(['after', 'before', 'on']);
        dataPush['criteria'] = 'after';
      } else if (condition.type === 5) {
        this._audienceService.criteriaForFilters(['is']);
        dataPush['criteria'] = 'is';
      }
      dataPush['name'] = condition.name;
      dataPush['type'] = condition.type;
      dataPush['customFieldID'] = condition.customFieldID;
      dataPush['value'] = null;
    } else {
      this._audienceService.createValueConditionList('none');
      this._audienceService.criteriaForFilters(['is', 'isn\'t']);
      if (condition.type === 'tags') {
        dataPush['tagID'] = 0;
        dataPush['criteria'] = 'is';
        dataPush['name'] = condition.description;
        dataPush['value'] = '';
      } else if (condition.type === 'widgets') {
        dataPush['widgetID'] = 0;
        dataPush['criteria'] = 'is';
        dataPush['name'] = condition.description;
        dataPush['value'] = '';
      } else if (condition.type === 'sequences') {
        dataPush['sequenceID'] = 0;
        dataPush['criteria'] = 'is';
        dataPush['name'] = condition.description;
        dataPush['value'] = '';
      }
    }
    this._sharedService.conditionArray.push(dataPush);
    setTimeout(() => {
      this._builderService.getPointsPosition(this._builderService.config, this._builderService.elem);
    }, 10);
    this._sharedService.noneConditionButton = false;
    this._sharedService.activeCondition = !this._sharedService.activeCondition;
  }

  /**
   * Outside clicked
   * @param e {Event}
   */
  public onClickedOutside(e: Event) {
    this._sharedService.noneConditionButton = false;
    this._sharedService.activeCondition = !this._sharedService.activeCondition;
  }

}
