import { Component, Input, OnInit } from '@angular/core';
import { DragNDropComponent } from '../../../drag-n-drop/drag-n-drop.component';
import { Item } from '../../../builder/builder-interface';
import { BuilderService } from '../../../services/builder.service';
import { BuilderFunctionsService } from '../../../services/builder-functions.service';
import { ArrowsService } from '../../../services/arrows.service';
import { Router } from '@angular/router';
import { CompanyManagerService } from '../../../../../services/company-manager.service';
import { UserService } from '../../../../../services/user.service';

@Component({
  selector: 'app-send-message',
  templateUrl: './send-message.component.html',
  styleUrls: ['./send-message.component.scss', '../../../assets/general-style.scss']
})
export class SendMessageComponent extends DragNDropComponent implements OnInit {

  @Input() config: Item;

  constructor(
    public readonly _builderService: BuilderService,
    public readonly _builderFunctionsService: BuilderFunctionsService,
    public readonly _arrowsService: ArrowsService,
    public readonly companyManagerService: CompanyManagerService,
    public readonly _userService: UserService,
    private readonly _router: Router,
  ) {
    super(_builderService);
  }

  ngOnInit() {
    super.ngOnInit();
  }

  /**
   * Redirect to responder
   */
  public redirectToResponder() {
    const urlArr = window.location.pathname.split('/');
    const url = window.location.pathname;
    const id = this._builderService.requestDataAll.id;

    urlArr.forEach(item => {
      switch (item) {
        case 'sequences':
          this._router.navigate([url + '/flow/' + id + '/responses/' + this.config.id]);
          break;
        case 'keywords':
          this._router.navigate([url + '/flow/' + id + '/responses/' + this.config.id]);
          break;
        case 'growth-tools':
          this._router.navigate([url + '/responses/' + this.config.id]);
          break;
        case 'broadcasts':
          this._router.navigate([url + id + '/responses/' + this.config.id]);
          break;
        case 'default':
          this._router.navigate([url + '/' + id + '/responses/' + this.config.id]);
          break;
        case 'welcome':
          this._router.navigate([url + '/' + id + '/responses/' + this.config.id]);
          break;
        case 'content':
          this._router.navigate([url + '/responses/' + this.config.id]);
          break;
        case 'company-manager':
          this.companyManagerService.activeCampaignItem.type = 'flowResponses';
          this.companyManagerService.dataSecondLevelTabs.forEach((data) => {
            if (data.id === this.companyManagerService.activeCampaignItem.id) {
              data.type = 'flowResponses';
            }
          });
          this._router.navigate(['/' + this._userService.userID + '/company-manager'],
            {queryParams: {view: 'flowResponses', id: this.companyManagerService.activeCampaignItem.id, flowId: this.config.id}});
          break;
        default:
          break;
      }
    });
  }

}
