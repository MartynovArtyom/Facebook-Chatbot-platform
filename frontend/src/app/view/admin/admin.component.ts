import { Component, OnInit } from '@angular/core';
import { SharedService } from '../../services/shared.service';

@Component({
  selector: 'app-admin',
  templateUrl: './admin.component.html',
  styleUrls: ['./admin.component.scss']
})
export class AdminComponent implements OnInit {

  constructor(
    public sharedService: SharedService
  ) { }

  ngOnInit() {
    this.sharedService.sidebarView = localStorage.getItem('sidebarPosition');
    this.sharedService.preloaderView = true;
    setTimeout(() => {
      this.sharedService.preloaderView = false;
    }, 1500);
  }

}
