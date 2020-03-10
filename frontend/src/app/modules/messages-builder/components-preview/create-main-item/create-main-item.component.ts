import { Component, Input } from '@angular/core';
import { BuilderService } from '../../services/builder.service';
import { UUID } from 'angular2-uuid';
import {
  Arrow, ChildClass, ConditionClass, ItemClass, RandomItemClass, RandomizerClass,
  SmartDelayChildClass, StartAnotherFlow, TextChildClass
} from '../../builder/builder-interface';

@Component({
  selector: 'app-create-main-item',
  templateUrl: './create-main-item.component.html',
  styleUrls: [
    './create-main-item.component.scss',
  '../../assets/general-style.scss']
})
export class CreateMainItemComponent {

  @Input() size: number;
  public listMainItems = [];
  @Input() positionX: number;
  @Input() positionY: number;
  @Input() container: HTMLElement;
  public openListMainItemsCheck = false;

  constructor(
    private readonly _builderService: BuilderService
  ) {
    this.listMainItems = _builderService.listMainItems;
  }

  public openListMainItems(value) {
    this.openListMainItemsCheck = value;
  }

  public createMainItemComponent(type, name) {

    const containerWidth = this.container.clientWidth;
    const containerHeight = this.container.clientHeight;
    const x = this.positionX > 0 ? -this.positionX : Math.abs(this.positionX);
    const y = this.positionY > 0 ? -this.positionY : Math.abs(this.positionY);

    this.openListMainItemsCheck = false;
    const dataId = UUID.UUID();
    const data = new ItemClass({
      uuid: dataId,
      arrow: {
        to: new Arrow({}),
        from: new Arrow({
          id: dataId,
          fromItemX: x + containerWidth / 2 + 200,
          fromItemY: y + containerHeight / 2 - 125
        })
      },
      type: type,
      name: name,
      start_step: false,
      next_step: null,
      widget_content: [],
      quick_reply: [],
      x: x + containerWidth / 2 - 200,
      y: y + containerHeight / 2 - 125
    });
    this.switchMain(type, data.widget_content);

    this._builderService.requestDataItems.push(data);
    this._builderService.updateDraftItem();
  }

  /**
   * Switch main item type
   * @param type {string}
   * @param data {object}
   */
  public switchMain(type, data): any {
    switch (type) {
      case 'send_message':
        data.push(new ChildClass({
          uuid: UUID.UUID(),
          type: 'text',
          params: new TextChildClass({})
        }));
        break;
      case 'condition':
        const id1 = UUID.UUID();
        const id2 = UUID.UUID();
        data.push(new ConditionClass({
          valid_step: {
            uuid: id1,
            arrow: {
              from: new Arrow({
                id: id1
              }),
              to: new Arrow({})
            }
          },
          invalid_step: {
            uuid: id2,
            arrow: {
              from: new Arrow({
                id: id2
              }),
              to: new Arrow({})
            }
          }
        }));
        break;
      case 'start_another_flow':
        data.push(new StartAnotherFlow({}));
        break;
      case 'randomizer':
        const i1 = UUID.UUID();
        const i2 = UUID.UUID();
        data.push(new RandomizerClass({
          randomData: [
            new RandomItemClass({
              random_leter: 'A',
              uuid: i1,
              arrow: {
                from: new Arrow({
                  id: i1
                }),
                to: new Arrow({})
              }
            }),
            new RandomItemClass({
              random_leter: 'B',
              uuid: i2,
              arrow: {
                from: new Arrow({
                  id: i2
                }),
                to: new Arrow({})
              }
            })]
        }));
        break;
      case 'smart_delay':
        data.push(new SmartDelayChildClass({}));
        break;
    }
  }

}
