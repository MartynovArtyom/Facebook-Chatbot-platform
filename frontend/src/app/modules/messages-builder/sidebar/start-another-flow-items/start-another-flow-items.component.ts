import { Component, Input, OnInit } from '@angular/core';
import { Item } from '../../builder/builder-interface';
import { BuilderFunctionsService } from '../../services/builder-functions.service';
import { UserService } from '../../../../services/user.service';
import { ActivatedRoute, Router } from '@angular/router';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { BuilderService } from '../../services/builder.service';
import { CompanyManagerService } from '../../../../services/company-manager.service';

@Component({
  selector: 'app-start-another-flow-items',
  templateUrl: './start-another-flow-items.component.html',
  styleUrls: [
    '../../assets/general-style.scss',
    './start-another-flow-items.component.scss'
  ]
})
export class StartAnotherFlowItemsComponent implements OnInit {

  @Input() config: Item;
  public modalActiveClose: any;
  public queryParams: any;

  constructor(
    public _builderFunctionsService: BuilderFunctionsService,
    public _userService: UserService,
    public _builderService: BuilderService,
    public _modalService: NgbModal,
    public _router: Router,
    public companyManagerService: CompanyManagerService,
    public route: ActivatedRoute
  ) {
    this.route.queryParams.subscribe(data => {
      this.queryParams = data;
    });
  }

  ngOnInit() {
  }

  /**
   * Open flow
   * @param data {object}
   */
  public openFlow(data) {
    if (this.queryParams && this.queryParams.view) {
      if (data.type_selected_parent_item === 'autoresponder') {
        this.openAutoresponder(data);
      } else if (data.type_selected_parent_item === 'company' ||
        data.type_selected_parent_item === 'newAutoresponder' ||
        data.type_selected_parent_item === 'keywords') {

        let selectItem = {};
        let count = 0;
        this.companyManagerService.statusCampaign = false;
        this.companyManagerService.dataFirstLevelTabs.forEach(item => {
          item.status = false;
          if (item.id === data.id_selected_parent_item) {
            count++;
            selectItem = item;
          }
        });

        if (count === 0) {
          this.createStartAnotherTab(data);
        } else {
          selectItem['status'] = true;
          this.companyManagerService.statusCampaign = false;

          this.companyManagerService.dataSecondLevelTabs = selectItem['children'];
          selectItem['children'].forEach((it, index) => {
            it.status = false;
            if (index === 0) {
              this.companyManagerService.activeCampaignItem = it;
              it.status = true;
            }
          });

          if (data.type_selected_parent_item === 'newAutoresponder' ||
            data.type_selected_parent_item === 'keywords') {
            this._router.navigate(['/' + this._userService.userID + '/company-manager'],
              {queryParams: {view: this.companyManagerService.activeCampaignItem['type'],
                  id: this.companyManagerService.activeCampaignItem['id']}});
          } else {
            this._router.navigate(['/' + this._userService.userID + '/company-manager'],
              {queryParams: {view: selectItem['type'], id: selectItem['id']}});
          }
        }

      } else {
        let count = 0;
        this.companyManagerService.dataSecondLevelTabs.forEach(item => {
          item.status = false;
          if (item.id === data.id_select_flow) {
            count++;
            item.status = true;

            this._router.navigate(['/' + this._userService.userID + '/company-manager'],
              {queryParams: {view: item.type, id: item.id}});
          }
        });

        if (count === 0) {
          const newTab = {
            name: data.name_select_flow,
            type: 'flow',
            status: true,
            id: data.id_select_flow
          };

          this.companyManagerService.dataSecondLevelTabs.push(newTab);
          this.companyManagerService.activeCampaignItem = newTab;

          this._router.navigate(['/' + this._userService.userID + '/company-manager'],
            {queryParams: {view: newTab.type, id: newTab.id}});
        }
      }
    } else {
      this._router.navigate(['/' + this._userService.userID + '/content/' + this.config.widget_content[0]['id_select_flow']]);
    }
  }

  /**
   * Create tab
   * @param data {object}
   */
  public createStartAnotherTab(data) {
    this.companyManagerService.dataFirstLevelTabs.forEach(item => {
      if (item.status) {
        item.status = false;
      }
    });
    this.companyManagerService.statusCampaign = false;

    const newTab = {
      name: data.name_select_flow,
      type: data.type_selected_parent_item,
      status: true,
      id: data.id_selected_parent_item,
      children: []
    };

    switch (data.type_selected_parent_item) {
      case 'company':
        newTab.type = 'widget';
        newTab.children.push({
          name: 'die Einstellungen',
          type: 'widget',
          status: false,
          id: data.id_selected_parent_item
        });
        newTab.children.push({
          name: 'Bearbeiten',
          type: 'flow',
          status: true,
          id: data.id_select_flow
        });
        break;
      case 'autoresponder':
        newTab.type = 'autoresponder';
        newTab.name = data.name_tab;
        newTab.children.push({
          name: 'Mitteilungen',
          type: 'autoresponder',
          status: false,
          id: data.id_selected_parent_item
        });
        newTab.children.push({
          name: data.name_select_flow,
          type: 'flow',
          status: true,
          id: data.id_select_flow
        });
        break;
      case 'newAutoresponder':
        newTab.type = 'newAutoresponder';
        newTab.children.push({
          name: 'Bearbeiten',
          type: 'flow',
          status: true,
          id: data.id_select_flow
        });
        break;
      case 'keywords':
        newTab.type = 'keywords';
        newTab.children.push({
          name: 'Bearbeiten',
          type: 'flow',
          status: true,
          id: data.id_select_flow
        });
        break;
      default:
        break;
    }

    const status = this.companyManagerService.dataFirstLevelTabs.filter(item => item.id === newTab.id && item.type === newTab.type);
    if (!status || status.length === 0) {
      this.companyManagerService.dataFirstLevelTabs.push(newTab);
    } else {
      status[0].status = true;
    }

    this.companyManagerService.dataSecondLevelTabs = newTab.children;
    newTab.children.forEach(tab => {
      if (tab.status) {
        this.companyManagerService.activeCampaignItem = tab;
      }
    });

    this._router.navigate(['/' + this._userService.userID + '/company-manager'],
      {queryParams: {view: this.companyManagerService.activeCampaignItem.type,
          id: this.companyManagerService.activeCampaignItem.id}});
  }

  /**
   * Open autoresponder tab
   * @param data {object}
   */
  public openAutoresponder(data) {
    const index = this.companyManagerService.dataFirstLevelTabs.findIndex(tab => tab.id === data.id_selected_parent_item);

    if (index !== -1) {
      this.companyManagerService.dataFirstLevelTabs.forEach((item, i) => {
        (i === index) ? item.status = true : item.status = false;
      });

      this.companyManagerService.dataSecondLevelTabs = this.companyManagerService.dataFirstLevelTabs[index].children;
      const secondIndex = this.companyManagerService.dataSecondLevelTabs.findIndex(tab => tab.id === data.id_select_flow);
      if (secondIndex !== -1) {
        this.companyManagerService.dataSecondLevelTabs.forEach((item, i) => {
          (i === secondIndex) ? item.status = true : item.status = false;
        });
        this.companyManagerService.activeCampaignItem = this.companyManagerService.dataSecondLevelTabs[secondIndex];
        this._router.navigate(['/' + this._userService.userID + '/company-manager'],
          {queryParams: {view: this.companyManagerService.activeCampaignItem.type,
              id: this.companyManagerService.activeCampaignItem.id}});
      } else {
        this.companyManagerService.dataSecondLevelTabs.forEach((item, i) => {
          item.status = false;
        });
        this.companyManagerService.dataSecondLevelTabs.push({
          name: data.name_select_flow,
          type: 'flow',
          status: true,
          id: data.id_select_flow
        });
        this.companyManagerService.activeCampaignItem =
          this.companyManagerService.dataSecondLevelTabs[this.companyManagerService.dataSecondLevelTabs.length - 1];
        this._router.navigate(['/' + this._userService.userID + '/company-manager'],
          {queryParams: {view: 'flow', id: data.id_select_flow}});
      }
    } else {
      this.companyManagerService.dataFirstLevelTabs.forEach(item => {
        if (item.status) {
          item.status = false;
        }
      });
      this.companyManagerService.statusCampaign = false;

      const newTab = {
        name: data.name_tab,
        type: 'autoresponder',
        status: true,
        id: data.id_selected_parent_item,
        children: []
      };

      newTab.children.push({
        name: 'Mitteilungen',
        type: 'autoresponder',
        status: false,
        id: data.id_selected_parent_item
      });
      newTab.children.push({
        name: data.name_select_flow,
        type: 'flow',
        status: true,
        id: data.id_select_flow
      });
      this.companyManagerService.dataFirstLevelTabs.push(newTab);
      this.companyManagerService.dataSecondLevelTabs = newTab.children;
      this.companyManagerService.activeCampaignItem = newTab.children[1];
      this._router.navigate(['/' + this._userService.userID + '/company-manager'],
        {queryParams: {view: this.companyManagerService.activeCampaignItem.type, id: this.companyManagerService.activeCampaignItem.id}});
    }
  }

  /**
   * Delete flow info
   */
  public deleteFlow() {
    this.config.widget_content[0]['name_select_flow'] = '';
    this.config.widget_content[0]['id_select_flow'] = null;
    this._builderService.updateDraftItem();
  }

  /**
   * Open popup
   * @param content {any}
   */
  public openVerticallyCentered(content) {
    this.modalActiveClose = this._modalService.open(content, {
      'size': 'lg',
      windowClass: 'flow-popup',
      backdropClass: 'light-blue-backdrop'});
  }

}
