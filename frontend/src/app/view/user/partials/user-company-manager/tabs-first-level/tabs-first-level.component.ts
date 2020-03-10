import { Component, OnInit } from '@angular/core';
import { CompanyManagerService } from '../../../../../services/company-manager.service';

@Component({
  selector: 'app-tabs-first-level',
  templateUrl: './tabs-first-level.component.html',
  styleUrls: ['./tabs-first-level.component.scss']
})
export class TabsFirstLevelComponent implements OnInit {

  constructor(
    public companyManagerService: CompanyManagerService
  ) { }

  ngOnInit() {
    document.getElementById('horizontal-scroller')
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

}
