import { Component, Input, OnInit } from '@angular/core';
import { UserService } from '../../../../services/user.service';
import { Router } from '@angular/router';
import { MainMenuService } from '../../../../services/main-menu.service';
import { BuilderService } from '../../../messages-builder/services/builder.service';
import { SharedService } from '../../../../services/shared.service';

@Component({
  selector: 'app-selected-bar-start-step',
  templateUrl: './selected-bar-start-step.component.html',
  styleUrls: ['./selected-bar-start-step.component.scss']
})
export class SelectedBarStartStepComponent implements OnInit {

  public _data: any;
  public _config: any;
  public _changePreloader: any;
  public _returnData: any;

  @Input() closePopup;

  @Input('changePreloader') set changePreloader(changePreloader) {
    this._changePreloader = changePreloader;
  }
  get changePreloader() {
    return this._changePreloader;
  }

  @Input('data') set data(data) {
    if (data) {
      this._data = data;
    }
  }
  get data() {
    return this._data;
  }

  @Input('config') set config(config) {
    if (config) {
      this._config = config;
    }
  }
  get config() {
    return this._config;
  }

  @Input('returnData') set returnData(returnData) {
    if (returnData) {
      this._returnData = returnData;
    }
  }
  get returnData() {
    return this._returnData;
  }

  constructor(
    private _builderService: BuilderService,
    private _sharedService: SharedService,
    private _mainMenuService: MainMenuService,
    private _router: Router,
    private _userService: UserService
  ) { }

  ngOnInit() {

  }

  /**
   * Select flow to content
   * @param data {object}
   */
  public async selectFlowToContent(data): Promise<any> {
    let flowItemID = null;
    data.items.forEach(item => {
      if (item.start_step) {
        flowItemID = item.id;
      }
    });

    try {
      this._returnData = await this._userService.getFlowItemByIdSelect(this._userService.userID, data.id, flowItemID);
      this.closePopup(this._returnData);
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

}
