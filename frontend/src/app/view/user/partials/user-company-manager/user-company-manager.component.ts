import { Component, OnInit } from '@angular/core';
import { CompanyManagerService } from '../../../../services/company-manager.service';

@Component({
  selector: 'app-user-company-manager',
  templateUrl: './user-company-manager.component.html',
  styleUrls: ['./user-company-manager.component.scss']
})
export class UserCompanyManagerComponent implements OnInit {

  constructor(
    public companyManagerService: CompanyManagerService
  ) {
    companyManagerService.dataFirstLevelTabs.forEach(item => {
      if (item.status) {
        item.status = false;
      }
    });
    companyManagerService.dataSecondLevelTabs = [];
    companyManagerService.statusCampaign = true;
  }

  ngOnInit() {
  }

}
