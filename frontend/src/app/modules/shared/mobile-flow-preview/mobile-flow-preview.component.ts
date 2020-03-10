import { Component, Input, OnChanges, OnInit } from '@angular/core';
import { SharedService } from '../../../services/shared.service';
import { UserService } from '../../../services/user.service';

@Component({
  selector: 'app-mobile-flow-preview',
  templateUrl: './mobile-flow-preview.component.html',
  styleUrls: ['./mobile-flow-preview.component.scss']
})
export class MobileFlowPreviewComponent implements OnInit, OnChanges {

  @Input() public id: any;
  @Input() public config: any;
  public data: any;
  public preloadContent = true;

  constructor(
    private readonly _sharedService: SharedService,
    private readonly _userService: UserService
  ) { }

  ngOnInit() {
    if (this.id) {
      this.getFlowById();
    } else {
      this.preloadContent = false;
      this.config.forEach((item) => {
        if (item.start_step) {
          this.data = item;
        }
      });
    }
  }

  ngOnChanges() {
    if (this.id) {
      this.getFlowById();
    } else {
      this.preloadContent = false;
      this.config.forEach((item) => {
        if (item.start_step) {
          this.data = item;
        }
      });
    }
  }

  /**
   * Get flow by id
   * @returns {Promise<void>}
   */
  public async getFlowById(): Promise<any> {
    this.preloadContent = true;
    try {
      const data = await this._userService.getFlowItemById(this._userService.userID, this.id);
      if (data.items && data.items.length > 0) {
        data.items.forEach((item) => {
          if (item.start_step) {
            this.data = item;
          }
        });
      } else {
        this.data = {
          widget_content: [{
            type: 'text',
            params: {
              description: 'Hello',
              button: []
            }
          }],
          quick_reply: []
        };
      }
      this.preloadContent = false;
    } catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Change position cards
   * @param card {object}
   * @param cards_array {array}
   * @param i {number}
   * @param action {string}
   */
  public changePositionCards(card, cards_array, i, action) {
    if (action === 'prev') {
      if (i > 0) {
        card.active = false;
        cards_array[i - 1].active = true;
      }
    } else if (action === 'next') {
      if (i < (cards_array.length - 1)) {
        card.active = false;
        cards_array[i + 1].active = true;
      }
    }
  }


}
