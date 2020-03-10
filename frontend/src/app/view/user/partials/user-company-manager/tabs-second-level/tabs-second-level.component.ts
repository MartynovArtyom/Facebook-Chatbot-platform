import { Component, OnInit } from '@angular/core';
import { CompanyManagerService } from '../../../../../services/company-manager.service';
import { ActivatedRoute } from '@angular/router';

@Component({
  selector: 'app-tabs-second-level',
  templateUrl: './tabs-second-level.component.html',
  styleUrls: ['./tabs-second-level.component.scss']
})
export class TabsSecondLevelComponent implements OnInit {
  public view: any;
  public preloader = true;

  constructor(
    public companyManagerService: CompanyManagerService,
    public _route: ActivatedRoute
  ) {
    this._route.queryParams.subscribe((data) => {
      if (!this.view ||
          this.view.view !== data.view ||
          this.view.id !== data.id) {
        this.renderView(data);
      }
    });
  }

  ngOnInit() {
    document.getElementById('horizontal-scroller-second')
      .addEventListener('wheel', function(event) {
        let modifier;
        if (event.deltaMode === event.DOM_DELTA_PIXEL) {
          modifier = 1;
          // иные режимы возможны в Firefox
        } else if (event.deltaMode === event.DOM_DELTA_LINE) {
          modifier = parseInt(getComputedStyle(this).lineHeight);
        } else if (event.deltaMode === event.DOM_DELTA_PAGE) {
          modifier = this.clientHeight;
        }
        if (event.deltaY !== 0) {
          // замена вертикальной прокрутки горизонтальной
          this.scrollLeft += modifier * event.deltaY;
          event.preventDefault();
        }
      });
  }

  public renderView(data) {
    this.preloader = true;
    if (data.view && data.id) {
      this.view = data;
      setTimeout(() => {
        this.preloader = false;
      }, 100);
    }
  }

}
