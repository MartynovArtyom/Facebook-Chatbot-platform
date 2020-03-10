import { ComponentFactoryResolver, ComponentRef, Directive, Input, OnChanges, OnInit, Renderer2, ViewContainerRef } from '@angular/core';
import { Item } from '../builder/builder-interface';
import { SendMessageChildsComponent } from '../components-preview/child-items/send-message-childs/send-message-childs.component';
import { PerformActionsChildsComponent } from '../components-preview/child-items/perform-actions-childs/perform-actions-childs.component';
import {
  StartAnotherFlowChildsComponent
} from '../components-preview/child-items/start-another-flow-childs/start-another-flow-childs.component';
import { ConditionChildsComponent } from '../components-preview/child-items/condition-childs/condition-childs.component';
import { RandomizerChildsComponent } from '../components-preview/child-items/randomizer-childs/randomizer-childs.component';
import { SmartDelayChildsComponent } from '../components-preview/child-items/smart-delay-childs/smart-delay-childs.component';

const components = {
  'send_message': SendMessageChildsComponent,
  'perform_actions': PerformActionsChildsComponent,
  'start_another_flow': StartAnotherFlowChildsComponent,
  'condition': ConditionChildsComponent,
  'randomizer': RandomizerChildsComponent,
  'smart_delay': SmartDelayChildsComponent
};

@Directive({
  selector: '[appDynamicChild]'
})
export class DynamicChildDirective implements OnInit, OnChanges {

  @Input() config: Item;
  private _component: ComponentRef<any>;

  constructor(
    private readonly resolver: ComponentFactoryResolver,
    private readonly container: ViewContainerRef,
    private readonly _renderer: Renderer2,
  ) { }

  ngOnChanges() {
    if (this._component) {
      this._component.instance.config = this.config;
    }
  }

  ngOnInit() {
    if (!components[this.config.type]) {
      throw new Error('Trying to use an unsupported type (' + this.config.type + ')');
    }
    const component = this.resolver.resolveComponentFactory<Item>(components[this.config.type]);
    this._component = this.container.createComponent(component);
    this._renderer.addClass(this._component.location.nativeElement, 'child-container');
    this._component.instance.config = this.config;
  }

}
