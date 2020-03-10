import { Component, DoCheck, IterableDiffers, OnInit } from '@angular/core';
import { BuilderService } from '../services/builder.service';
import { Arrow, IArrow } from '../builder/builder-interface';
import { ArrowsService } from '../services/arrows.service';
import set = Reflect.set;
import { BuilderFunctionsService } from '../services/builder-functions.service';

@Component({
  selector: 'app-arrow',
  templateUrl: './arrow.component.html',
  styleUrls: ['./arrow.component.scss']
})
export class ArrowComponent implements OnInit, DoCheck {

  public arrows = Array<IArrow>();
  private differ: any;
  public arr: any;
  public openBtn = false;

  constructor(
    public readonly _builderService: BuilderService,
    private readonly _builderFunctionService: BuilderFunctionsService,
    public readonly _arrowsService: ArrowsService,
    private readonly _differs: IterableDiffers
  ) {
    this.differ = _differs.find([]).create(null);
  }

  ngDoCheck() {
    const change = this.differ.diff(this._builderService.requestDataItems);
    if (change) {
      this._arrowsService.linksArray = [];
      this._builderService.requestDataItems.forEach(data => {
        if (data.arrow.to.id) {
          this._arrowsService.linksArray.push({
            to: data.arrow.to.id,
            toArr: [{ fromObj: data.arrow.from, toObj: data.arrow.to }],
          });
        }

        if (data.type === 'send_message') {
          data.widget_content.forEach((item) => {
            if (item.type === 'text' || item.type === 'image' || item.type === 'video' || item.type === 'user_input') {
              item.params.buttons.forEach((button) => {
                if (button.arrow.to.id) {
                  this._arrowsService.linksArray.push({
                    to: button.arrow.to.id,
                    toArr: [{ fromObj: button.arrow.from, toObj: button.arrow.to }],
                  });
                }
              });
            } else if (item.type === 'card' || item.type === 'gallery') {
              item.params.cards_array.forEach((card) => {
                card.buttons.forEach((button) => {
                  if (button.arrow.to.id) {
                    this._arrowsService.linksArray.push({
                      to: button.arrow.to.id,
                      toArr: [{ fromObj: button.arrow.from, toObj: button.arrow.to }],
                    });
                  }
                });
              });
            }
          });

          data.quick_reply.forEach((item) => {
            item.buttons.forEach((button) => {
              if (button.arrow.to.id) {
                this._arrowsService.linksArray.push({
                  to: button.arrow.to.id,
                  toArr: [{ fromObj: button.arrow.from, toObj: button.arrow.to }],
                });
              }
            });
          });
        } else if (data.type === 'condition') {
          if (data.widget_content[0].invalid_step.arrow.to.id) {
            this._arrowsService.linksArray.push({
              to: data.widget_content[0].invalid_step.arrow.from.id,
              toArr: [{
                fromObj: data.widget_content[0].invalid_step.arrow.from,
                toObj: data.widget_content[0].invalid_step.arrow.to
              }],
            });
          }
          if (data.widget_content[0].valid_step.arrow.to.id) {
            this._arrowsService.linksArray.push({
              to: data.widget_content[0].valid_step.arrow.from.id,
              toArr: [{
                fromObj: data.widget_content[0].valid_step.arrow.from,
                toObj: data.widget_content[0].valid_step.arrow.to
              }],
            });
          }
        } else if (data.type === 'randomizer') {
          data.widget_content[0].randomData.forEach((item) => {
            if (item.arrow.to.id) {
              this._arrowsService.linksArray.push({
                to: item.arrow.to.id,
                toArr: [{ fromObj: item.arrow.from, toObj: item.arrow.to }],
              });
            }
          });
        }
      });
    }
  }

  ngOnInit() {

  }

  /**
   * Delete link
   */
  public deleteLink() {
    this._builderFunctionService.checkPANextStep(this.arr.toObj.id);
    this._builderService.requestDataItems.forEach(item => {
      if (item.type === 'send_message') {
        if (item.uuid === this.arr.fromObj.id) {
          item.arrow.to = new Arrow({});
          item.next_step = null;
        }
        item.widget_content.forEach((data) => {
          if (data.type === 'text' || data.type === 'image' || data.type === 'video' || data.type === 'user_input') {
            data.params.buttons.forEach((button) => {
              if (button.uuid === this.arr.fromObj.id) {
                button.arrow.to = new Arrow({});
                button.next_step = null;
                if (button.type !== 'open_website') {
                  button.type = null;
                }
              }
            });
          } else if (data.type === 'card' || data.type === 'gallery') {
            data.params.cards_array.forEach((card) => {
              card.buttons.forEach((button) => {
                if (button.uuid === this.arr.fromObj.id) {
                  button.arrow.to = new Arrow({});
                  button.next_step = null;
                  if (button.type !== 'open_website') {
                    button.type = null;
                  }
                }
              });
            });
          }
        });

        item.quick_reply.forEach((data) => {
          if (data.uuid === this.arr.fromObj.id) {
            data.buttons[0].arrow.to = new Arrow({});
            data.next_step = null;
            data.type = null;
            if (data.type !== 'open_website') {
              data.type = null;
            }
          }
        });
      } else if (item.type === 'randomizer') {
        item.widget_content[0].randomData.forEach((data) => {
          if (data.uuid === this.arr.fromObj.id) {
            data.arrow.to = new Arrow({});
            data.next_step = null;
            data.type = null;
          }
        });
      } else if (item.type === 'condition') {
        if (item.widget_content[0].valid_step.uuid === this.arr.fromObj.id) {
          item.widget_content[0].valid_step.arrow.to = new Arrow({});
          item.widget_content[0].valid_step.next_step = null;
          item.widget_content[0].valid_step.type = null;
        }
        if (item.widget_content[0].invalid_step.uuid === this.arr.fromObj.id) {
          item.widget_content[0].invalid_step.arrow.to = new Arrow({});
          item.widget_content[0].invalid_step.next_step = null;
          item.widget_content[0].invalid_step.type = null;
        }
      } else {
        if (item.uuid === this.arr.fromObj.id) {
          item.arrow.to = new Arrow({});
          item.next_step = null;
        }
      }
    });
    this.openBtn = false;
    const index = this._arrowsService.linksArray.findIndex(link => link.toArr[0] === this.arr);
    this._arrowsService.linksArray.splice(index, 1);
    this.arr = null;
    this._builderService.updateDraftItem();
  }

  /**
   * Open delete link button
   * @param arrow {object}
   * @param value {boolean}
   */
  public openRemoveBtn(arrow, value) {
    this.arr = arrow;
    if (value === false) {
        this.openBtn = value;
    } else {
      this.openBtn = value;
    }
  }

}
