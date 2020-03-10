import { Component, DoCheck, Input, OnInit } from '@angular/core';
import { Item } from '../../builder/builder-interface';
import { BuilderFunctionsService } from '../../services/builder-functions.service';
import { SharedService } from '../../../../services/shared.service';
import { BuilderService } from '../../services/builder.service';

@Component({
  selector: 'app-condition-items',
  templateUrl: './condition-items.component.html',
  styleUrls: [
    '../../assets/general-style.scss',
    './condition-items.component.scss'
  ]
})
export class ConditionItemsComponent implements OnInit, DoCheck {

  @Input() config: Item;

  constructor(
    public _builderFunctionsService: BuilderFunctionsService,
    public _builderService: BuilderService,
    public _sharedService: SharedService
  ) {
  }

  ngOnInit() {
  }

  ngDoCheck() {
    this._sharedService.conditionArray = [];
    this._sharedService.conditionArray = this.config.widget_content[0]['conditions'];
  }

  /**
   * Sekect condition item
   */
  public activeConditionSelect() {
    setTimeout(() => {
      this._sharedService.noneConditionButton = true;
      this._sharedService.activeCondition = !this._sharedService.activeCondition;
    }, 100);
  }

}
