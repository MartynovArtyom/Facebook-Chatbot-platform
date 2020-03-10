import {
  ComponentFactoryResolver, ComponentRef, Directive, Input, OnChanges, OnInit, Renderer2, ViewContainerRef
} from '@angular/core';
import { Item } from '../builder/builder-interface';
import { SendMessageComponent } from '../components-preview/main-items/send-message/send-message.component';
import { PerformActionsComponent } from '../components-preview/main-items/perform-actions/perform-actions.component';
import { StartAnotherFlowComponent } from '../components-preview/main-items/start-another-flow/start-another-flow.component';
import { ConditionComponent } from '../components-preview/main-items/condition/condition.component';
import { RandomizerComponent } from '../components-preview/main-items/randomizer/randomizer.component';
import { SmartDelayComponent } from '../components-preview/main-items/smart-delay/smart-delay.component';

const components = {
  'send_message': SendMessageComponent,
  'perform_actions': PerformActionsComponent,
  'start_another_flow': StartAnotherFlowComponent,
  'condition': ConditionComponent,
  'randomizer': RandomizerComponent,
  'smart_delay': SmartDelayComponent,
};

@Directive({
  selector: '[appDynamicItems]'
})
export class DynamicItemsDirective implements OnInit, OnChanges {

  @Input() config: Item;
  @Input() scale: number;
  private _component: ComponentRef<any>;

  constructor(
    private readonly resolver: ComponentFactoryResolver,
    private readonly container: ViewContainerRef,
    private readonly _renderer: Renderer2,
  ) { }

  ngOnChanges() {
    if (this._component) {
      this._component.instance.config = this.config;
      this._component.instance.scale = this.scale;
    }
  }

  ngOnInit() {
    if (!components[this.config.type]) {
      throw new Error('Trying to use an unsupported type (' + this.config.type + ')');
    }
    const component = this.resolver.resolveComponentFactory<Item>(components[this.config.type]);
    this._component = this.container.createComponent(component);
    this._renderer.addClass(this._component.location.nativeElement, 'item-container');
    this._component.instance.config = this.config;
    this._component.instance.scale = this.scale;
  }

}
